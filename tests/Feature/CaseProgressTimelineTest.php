<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Case\CaseProgressLog;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CaseProgressTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_timeline_groups_calendar_days_and_keeps_latest_event_first(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $user = User::factory()->create();
        $this->grant($user, 'case.view');
        $case = $this->caseFor($user);

        $older = $this->logFor($case, 'internal', 'Older log entry');
        $newer = $this->logFor($case, 'ai_agent', 'Newer AI audit entry');
        DB::table('task_logs')->where('id', $older->id)
            ->update(['created_at' => '2026-11-10 09:15:01']);
        DB::table('task_logs')->where('id', $newer->id)
            ->update(['created_at' => '2026-11-11 15:42:35']);

        $this->actingAs($user);
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->assertSee('Times shown in Europe/London')
            ->assertSeeInOrder([
                'Wednesday, 11 November 2026',
                'Newer AI audit entry',
                'Tuesday, 10 November 2026',
                'Older log entry',
            ])
            ->assertSee('15:42:35')
            ->assertSee('09:15:01');
    }

    public function test_filters_keep_other_cases_private_and_show_only_matching_types(): void
    {
        $user = User::factory()->create();
        $this->grant($user, 'case.view');
        $case = $this->caseFor($user);
        $other = $this->caseFor($user);

        $this->logFor($case, 'internal', 'Internal note marker');
        $this->logFor($case, 'ai_agent', 'AI audit marker');
        $this->logFor($case, 'external', 'Customer note marker', [
            'external_user' => 'customer@example.test',
        ]);
        $this->logFor($case, 'internal', 'Urgent note marker', [
            'require_attention' => true,
        ]);
        $this->logFor($other, 'ai_agent', 'Other case private marker');

        $this->actingAs($user);
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->assertDontSee('Other case private marker')
            ->call('setActivityFilter', 'ai')
            ->assertSee('AI audit marker')
            ->assertDontSee('Internal note marker')
            ->assertDontSee('Customer note marker')
            ->call('setActivityFilter', 'customer')
            ->assertSee('Customer note marker')
            ->assertDontSee('AI audit marker')
            ->call('setActivityFilter', 'attention')
            ->assertSee('Urgent note marker')
            ->assertDontSee('Customer note marker')
            ->call('setActivityFilter', 'internal')
            ->assertSee('Internal note marker')
            ->assertDontSee('AI audit marker')
            ->call('setActivityFilter', 'all')
            ->assertSee('AI audit marker')
            ->assertSee('Internal note marker')
            ->assertSee('Customer note marker');
    }

    public function test_show_older_activity_preserves_order_and_filter_reset(): void
    {
        $user = User::factory()->create();
        $this->grant($user, 'case.view');
        $case = $this->caseFor($user);

        for ($i = 1; $i <= 21; $i++) {
            $this->logFor($case, 'internal', sprintf('History marker [%02d]', $i));
        }

        $this->actingAs($user);
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->assertSee('Show older activity')
            ->assertDontSee('History marker [01]')
            ->call('loadOlderActivity')
            ->assertSet('activityLimit', 40)
            ->assertSee('History marker [01]')
            ->assertDontSee('Show older activity')
            ->call('setActivityFilter', 'ai')
            ->assertSet('activityLimit', 20)
            ->assertSee('No matching activity')
            ->call('setActivityFilter', 'all')
            ->assertSee('History marker [21]');
    }

    public function test_ai_audit_entries_remain_read_only_after_timeline_redesign(): void
    {
        $user = User::factory()->create();
        $this->grant($user, 'case.view', 'case.update');
        $case = $this->caseFor($user);
        $audit = $this->logFor($case, 'ai_agent', 'Immutable AI audit entry');

        $this->actingAs($user);
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->assertSee('Immutable AI audit entry')
            ->call('editLog', $audit->id)
            ->assertForbidden();

        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->call('deleteLog', $audit->id)
            ->assertForbidden();

        $this->assertDatabaseHas('task_logs', [
            'id' => $audit->id,
            'deleted_at' => null,
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function logFor(tickets $case, string $source, string $description, array $extra = []): TaskLogs
    {
        return TaskLogs::query()->create(array_merge([
            'ticket_id' => $case->id,
            'name' => 'Progress entry',
            'short_description' => 'Progress entry',
            'description' => $description,
            'type' => 'Internal Update',
            'source' => $source,
            'visible_to_external' => false,
        ], $extra));
    }

    private function caseFor(User $user): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $user->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Case progress timeline test',
            'description' => 'Verify case progress timeline UI.',
            'contact_email' => 'customer@example.test',
            'contact_telephone' => '0123456789',
        ]);
    }
}
