<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Case\CaseStatus;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\SlaTaskPause;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseRetrieveTicketStatus;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CaseStatusAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_statuses_are_scoped_to_each_case_and_do_not_count_system_logs_as_internal_updates(): void
    {
        $staff = User::factory()->create();
        $first = $this->caseFor($staff);
        $second = $this->caseFor($staff);
        $this->logFor($first, 'system_log', 'internal');
        $this->logFor($second, 'internal_update_log', 'internal');

        $service = app(CaseRetrieveTicketStatus::class);
        $statuses = $service->retrieveStatuses([$first, $second]);
        $this->assertSame(2, $statuses['total']);
        $this->assertSame(1, $statuses[$first->id]['noUpdate']);
        $this->assertSame(0, $statuses[$second->id]['noUpdate']);
        $this->assertSame(0, $statuses[$first->id]['numberOfUrgentLogs']);
        $this->assertSame(0, $statuses[$second->id]['numberOfUrgentLogs']);

        $this->logFor($first, 'internal_update_log', 'internal');
        $this->assertSame(0, $service->retrieveStatus($first)['noUpdate']);
    }

    public function test_paused_sla_does_not_count_as_breach_or_near_breach(): void
    {
        $case = $this->caseFor(User::factory()->create());
        $case->update(['state' => CaseState::Paused]);
        $sla = slaTask::query()->create([
            'ticket_id' => $case->id,
            'name' => 'Paused deadline test',
            'state' => CaseState::Paused,
            'active' => true,
            'respond_by' => now()->subHour(),
            'fix_by' => now()->subMinutes(20),
            'paused_at' => now()->subHours(2),
        ]);
        SlaTaskPause::query()->create([
            'sla_task_id' => $sla->id,
            'paused_at' => $sla->paused_at,
            'remaining' => 3600,
            'remaining_fix_time' => 7200,
        ]);

        $status = app(CaseRetrieveTicketStatus::class)->retrieveStatus($case);
        $this->assertSame(1, $status['paused']);
        $this->assertSame(0, $status['taskBreached']);
        $this->assertSame('', $status['breachType']);
        $this->assertSame(0, $status['closeToBreachWarning']);
        $this->assertFalse(slaTask::query()->fixBreached()->whereKey($sla->id)->exists());
    }

    public function test_fix_and_response_breaches_are_detected_without_persisted_breach_flags(): void
    {
        $staff = User::factory()->create();
        $responseCase = $this->caseFor($staff);
        $fixCase = $this->caseFor($staff);

        slaTask::query()->create([
            'ticket_id' => $responseCase->id, 'name' => 'Response breach',
            'active' => true, 'state' => CaseState::Open,
            'respond_by' => now()->subMinutes(15), 'fix_by' => now()->addHours(2),
        ]);
        slaTask::query()->create([
            'ticket_id' => $fixCase->id, 'name' => 'Fix breach',
            'active' => true, 'state' => CaseState::Open,
            'respond_by' => now()->addHours(2), 'fix_by' => now()->subMinutes(15),
        ]);

        $service = app(CaseRetrieveTicketStatus::class);
        $this->assertSame('Respond By Breach', $service->retrieveStatus($responseCase)['breachType']);
        $this->assertSame('Fix By Breach', $service->retrieveStatus($fixCase)['breachType']);
        $this->assertSame(1, $service->retrieveStatus($responseCase)['taskBreached']);
        $this->assertSame(1, $service->retrieveStatus($fixCase)['taskBreached']);
    }

    public function test_dashboard_open_case_count_excludes_resolved_closed_completed_and_cancelled(): void
    {
        $staff = User::factory()->create();
        $this->caseFor($staff);
        $this->caseFor($staff, CaseState::In_Progress);
        $this->caseFor($staff, CaseState::Paused);
        $this->caseFor($staff, CaseState::Resolved);
        $this->caseFor($staff, CaseState::Closed);
        $this->caseFor($staff, CaseState::Cancelled);
        $this->caseFor($staff, CaseState::Completed);

        $utility = app(CaseTicketUtilityServices::class);
        $this->assertSame(3, $utility->getTotalOpenCases());

        $statuses = $utility->retrieveTicketStatuses();
        $this->assertSame(4, $statuses['total']);
        $this->assertSame(1, collect($statuses)->sum('resolved'));
        $this->assertSame(3, collect($statuses)->sum('unassigned'));
    }

    public function test_dashboard_aggregates_actual_currently_working_flag(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view');
        $working = $this->caseFor($staff, CaseState::In_Progress);
        $working->update(['currently_working' => true]);
        $this->caseFor($staff, CaseState::Resolved);

        $this->actingAs($staff);
        $component = Livewire::test(CaseStatus::class);
        $this->assertSame(1, $component->instance()->buildStatus()['currentlyWorking']);
        $this->assertSame(1, $component->instance()->buildStatus()['resolved']);
    }

    public function test_customer_urgent_and_visit_totals_remain_case_scoped(): void
    {
        $staff = User::factory()->create();
        $case = $this->caseFor($staff);
        $other = $this->caseFor($staff);

        TaskLogs::query()->create([
            'ticket_id' => $case->id,
            'name' => 'Urgent customer message',
            'type' => 'external_update_log',
            'source' => 'external',
            'require_attention' => true,
            'description' => 'Customer reply needs review.',
        ]);
        TaskLogs::query()->create([
            'ticket_id' => $other->id,
            'name' => 'Other case urgent log',
            'type' => 'external_update_log',
            'source' => 'external',
            'require_attention' => true,
            'description' => 'Do not include in first case.',
        ]);
        TaskSiteVisit::query()->create([
            'ticket_id' => $case->id,
            'short_description' => 'Engineer enroute',
            'enroute_at' => now(),
        ]);
        TaskSiteVisit::query()->create([
            'ticket_id' => $case->id,
            'short_description' => 'Engineer completed visit',
            'onsite_at' => now()->subHour(),
            'offsite_at' => now(),
        ]);

        $status = app(CaseRetrieveTicketStatus::class)->retrieveStatus($case);
        $this->assertSame(1, $status['numberOfUrgentLogs']);
        $this->assertNotNull($status['lastUrgentLogCreatedAt']);
        $this->assertNotNull($status['lastCustomerLogCreatedAt']);
        $this->assertSame(1, $status['totalVisits']);
        $this->assertSame(1, $status['currentlyEnroute']);
        $this->assertSame(0, $status['currentlyOnsite']);
        $this->assertTrue($status['visitScheduled']);
    }

    public function test_case_information_internal_update_accessor_matches_status_aggregation(): void
    {
        $staff = User::factory()->create();
        $case = $this->caseFor($staff);
        $this->logFor($case, 'system_log', 'internal');

        $this->assertFalse($case->has_internal_update);
        $this->assertSame(1, app(CaseRetrieveTicketStatus::class)->retrieveStatus($case)['noUpdate']);

        $this->logFor($case, 'internal_update_log', 'internal');
        $this->assertTrue($case->has_internal_update);
        $this->assertSame(0, app(CaseRetrieveTicketStatus::class)->retrieveStatus($case)['noUpdate']);
        $this->assertNotNull($case->latestInternalUpdate()->first());
    }

    public function test_status_component_requires_case_view_permission(): void
    {
        $unauthorized = User::factory()->create();
        $this->actingAs($unauthorized);
        Livewire::test(CaseStatus::class)->assertForbidden();
    }

    public function test_missing_sla_task_returns_nullable_dates_and_zero_sla_flags(): void
    {
        $case = $this->caseFor(User::factory()->create());
        $status = app(CaseRetrieveTicketStatus::class)->retrieveStatus($case);
        $this->assertNull($status['respondBy']);
        $this->assertNull($status['fixBy']);
        $this->assertSame(0, $status['taskBreached']);
        $this->assertSame(0, $status['paused']);
        $this->assertSame(0, $status['closeToBreachWarning']);
    }

    private function caseFor(User $user, CaseState $state = CaseState::Open): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $user->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => $state,
            'short_description' => 'Status aggregation regression case',
            'description' => 'Per-case status checks.',
            'contact_email' => 'test@example.test',
            'contact_telephone' => '0123456789',
        ]);
    }

    private function logFor(tickets $case, string $type, string $source): TaskLogs
    {
        return TaskLogs::query()->create([
            'ticket_id' => $case->id,
            'name' => 'Status test note',
            'short_description' => 'Status test note',
            'description' => 'Status test',
            'type' => $type,
            'source' => $source,
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }
}
