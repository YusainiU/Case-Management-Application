<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\TroubleshootingReview;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AITroubleshootingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_review_and_save_draft_without_changing_case_or_notifying_customer(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $analysis = $this->analysisFor($case);

        $this->actingAs($staff);
        Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->call('prepare')
            ->assertSee('Draft prepared')
            ->assertSet('sourceAnalysisId', $analysis->id)
            ->assertSet('confirmed', false);

        // Human attestation is mandatory.
        $component = Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->call('prepare')
            ->call('save')
            ->assertHasErrors(['confirmed']);

        $component->set('confirmed', true)
            ->set('draft', 'Verified diagnostic observations: user reported HTTP 500. No fix was applied yet. Next action: inspect server logs.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('saved to the internal Case Progress log');

        $log = DB::table('task_logs')->where('ticket_id', $case->id)
            ->where('source', 'ai_assisted')->first();
        $this->assertNotNull($log);
        $this->assertSame('Internal Update', $log->type);
        $this->assertSame($staff->id, $log->user_id);
        $this->assertSame(0, (int) $log->visible_to_external);
        $this->assertSame(0, (int) $log->require_attention);
        $this->assertNull($log->notification_sent_at);
        $this->assertStringContainsString('analysis #'.$analysis->id, $log->description);
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertNull($case->resolved_datetime);
        $this->assertNull($case->resolution_details);
    }

    public function test_viewer_cannot_prepare_or_save_troubleshooting_note(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->analysisFor($case);

        $this->actingAs($viewer);
        Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->call('prepare')->assertForbidden();
        Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->set('confirmed', true)
            ->set('draft', 'Unapproved user-provided troubleshooting note.')
            ->call('save')->assertForbidden();

        $this->assertDatabaseMissing('task_logs', ['ticket_id' => $case->id, 'source' => 'ai_assisted']);
    }

    public function test_case_change_after_draft_preparation_blocks_save(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $this->analysisFor($case);

        $this->actingAs($staff);
        $component = Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->call('prepare')->set('confirmed', true);

        // Direct DB change leaves the original timestamp unchanged, exercising
        // the case content snapshot rather than timestamp-only staleness.
        DB::table('tickets')->where('id', $case->id)
            ->update(['description' => 'Different customer issue without an HTTP error.']);

        $component->call('save')->assertHasErrors(['draft']);
        $this->assertDatabaseMissing('task_logs', ['ticket_id' => $case->id, 'source' => 'ai_assisted']);
    }

    public function test_no_completed_analysis_cannot_prepare_draft(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);

        $this->actingAs($staff);
        Livewire::test(TroubleshootingReview::class, ['case' => $case])
            ->call('prepare')->assertSee('No current completed Case Assistant analysis');

        $this->assertDatabaseMissing('task_logs', ['ticket_id' => $case->id, 'source' => 'ai_assisted']);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function analysisFor(tickets $case): AIAnalysis
    {
        return AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
            'result' => [
                'summary' => 'The Application Portal returned HTTP 500 during login; cause unknown.',
                'suggested_next_actions' => ['Check server-side application error logs.'],
                'warnings' => ['Root cause has not been established.'],
            ],
        ]);
    }

    private function caseFor(User $user): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $user->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Application Portal HTTP 500',
            'description' => 'Login returns HTTP 500. No troubleshooting recorded.',
            'contact_email' => 'customer@example.test',
            'contact_telephone' => '0123456789',
        ]);
    }
}
