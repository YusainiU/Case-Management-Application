<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\ResolutionReadiness;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIResolutionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewed_resolution_handoff_is_internal_and_does_not_resolve_case(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        $component = Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->assertSee('Resolution handoff prepared');

        $component->set('fixApplied', 'Updated server-side application configuration to correct the failing login endpoint.')
            ->set('verification', 'Retested portal login with an authorised test account; received HTTP 200 and loaded the dashboard.')
            ->set('remainingRisks', 'Monitor errors over the next business day.')
            ->set('verified', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('case remains unresolved');

        $handoff = TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_resolution_review')->firstOrFail();

        $this->assertSame($staff->id, $handoff->user_id);
        $this->assertSame('Internal Update', $handoff->type);
        $this->assertFalse((bool) $handoff->visible_to_external);
        $this->assertFalse((bool) $handoff->require_attention);
        $this->assertNull($handoff->notification_sent_at);
        $this->assertStringContainsString($note->number, $handoff->description);
        $this->assertStringContainsString('HTTP 200', $handoff->description);
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertNull($case->resolved_datetime);
        $this->assertNull($case->resolution);
        $this->assertNull($case->resolution_details);
        $this->assertNull($case->closed_datetime);
        $this->assertNull($case->resolved_by);
    }

    public function test_attestation_and_verified_fix_and_retest_are_required(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        $component = Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->call('save')
            ->assertHasErrors(['fixApplied', 'verification', 'verified']);

        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    public function test_no_reviewed_troubleshooting_note_cannot_be_used_as_resolution_evidence(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $unreviewed = TaskLogs::query()->create([
            'ticket_id' => $case->id,
            'user_id' => $staff->id,
            'type' => 'AI Agent',
            'source' => 'ai_agent',
            'description' => 'Suggested but unverified steps',
        ]);

        $this->actingAs($staff);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $unreviewed->id)
            ->call('prepare')
            ->assertHasErrors(['sourceLogId']);
    }

    public function test_view_only_staff_cannot_prepare_or_save_handoff(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $note = $this->reviewedNote($case, $viewer);

        $this->actingAs($viewer);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->assertForbidden();
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->call('save')
            ->assertForbidden();
    }

    public function test_changed_case_or_source_note_invalidates_handoff(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        $component = Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->set('fixApplied', 'Corrected the portal configuration and restarted the application safely.')
            ->set('verification', 'Retested successful portal login and confirmed a valid authenticated session.')
            ->set('verified', true);

        DB::table('task_logs')->where('id', $note->id)
            ->update(['description' => 'The original diagnosis was withdrawn.']);

        $component->call('save')->assertHasErrors(['sourceLogId']);
        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    public function test_resolved_case_cannot_save_a_prepared_handoff(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        $component = Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->set('fixApplied', 'The application configuration was corrected and the faulty endpoint repaired.')
            ->set('verification', 'Portal login succeeded after repair using a valid account and a clean browser.')
            ->set('verified', true);

        DB::table('tickets')->where('id', $case->id)
            ->update(['resolved_datetime' => now()]);

        $component->call('save')->assertSee('already resolved or closed');
        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    public function test_terminal_case_state_without_timestamp_cannot_prepare_handoff(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);
        DB::table('tickets')->where('id', $case->id)
            ->update(['state' => CaseState::Resolved->value]);

        $this->actingAs($staff);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->assertSee('Resolved or closed cases cannot receive a resolution handoff');

        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    public function test_resolve_checkbox_requires_standard_resolution_fields(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->set('fixApplied', 'Updated the application configuration and restarted the portal backend.')
            ->set('verification', 'Retested login successfully using a valid customer test account.')
            ->set('verified', true)
            ->set('resolveCase', true)
            ->call('save')
            ->assertHasErrors(['resolutionType', 'resolvedBy']);

        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    public function test_resolve_checkbox_rejects_invalid_resolution_type_or_resolver(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        $this->actingAs($staff);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->set('fixApplied', 'Updated the application configuration and restarted the portal backend.')
            ->set('verification', 'Retested login successfully using a valid customer test account.')
            ->set('verified', true)
            ->set('resolveCase', true)
            ->set('resolutionType', 'Unlisted AI Resolution')
            ->set('resolvedBy', 999999)
            ->call('save')
            ->assertHasErrors(['resolutionType', 'resolvedBy']);

        $this->assertSame(CaseState::Open, $case->refresh()->state);
    }

    public function test_resolve_checkbox_requires_sla_task_before_delegating(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $note = $this->reviewedNote($case, $staff);

        // No SLA task exists for this factory-created case.
        $this->actingAs($staff);
        Livewire::test(ResolutionReadiness::class, ['case' => $case])
            ->set('sourceLogId', $note->id)
            ->call('prepare')
            ->set('fixApplied', 'Corrected the faulty authentication configuration on the server.')
            ->set('verification', 'Retested portal login successfully and reviewed the server error logs.')
            ->set('verified', true)
            ->set('resolveCase', true)
            ->set('resolutionType', 'Configuration Changes')
            ->set('resolvedBy', $staff->id)
            ->call('save')
            ->assertHasErrors(['resolveCase']);

        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'source' => 'ai_resolution_review',
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function reviewedNote(tickets $case, User $user): TaskLogs
    {
        return TaskLogs::query()->create([
            'ticket_id' => $case->id,
            'user_id' => $user->id,
            'type' => 'Internal Update',
            'source' => 'ai_assisted',
            'description' => 'Reviewed troubleshooting findings and proposed next steps.',
            'visible_to_external' => false,
        ]);
    }

    private function caseFor(User $creator): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $creator->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Application Portal returns HTTP 500',
            'description' => 'The login endpoint returns a server error.',
            'contact_email' => 'customer@example.test',
            'contact_telephone' => '0123456789',
        ]);
    }
}
