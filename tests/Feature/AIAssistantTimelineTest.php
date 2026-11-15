<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\CaseAssistant;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIAssistantTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_shows_newest_ai_events_first_with_explicit_date_and_time(): void
    {
        config()->set('app.timezone', 'Europe/London');

        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $older = $this->logFor($case, 'ai_agent', 'AI:proposal:first:prepared',
            'Older AI category proposal');
        $newer = $this->logFor($case, 'ai_resolution_review',
            'Reviewed resolution handoff', 'Newest staff-reviewed handoff');

        DB::table('task_logs')->where('id', $older->id)
            ->update(['created_at' => '2026-11-10 09:15:01']);
        DB::table('task_logs')->where('id', $newer->id)
            ->update(['created_at' => '2026-11-11 15:42:35']);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Activity timeline')
            ->assertSee('Times shown in Europe/London')
            ->assertSeeInOrder(['Newest staff-reviewed handoff', 'Older AI category proposal'])
            ->assertSee('11 Nov 2026')
            ->assertSee('15:42:35')
            ->assertSee('10 Nov 2026')
            ->assertSee('09:15:01')
            ->assertSee('Category recommendations and approvals')
            ->assertSee('Resolution readiness and customer notification');
    }

    public function test_timeline_only_shows_current_cases_ai_logs(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $otherCase = $this->caseFor($viewer);

        $this->logFor($case, 'ai_assisted', 'Reviewed troubleshooting draft',
            'This case troubleshooting note');
        $this->logFor($case, 'internal', 'Unrelated internal comment',
            'Unrelated internal comment description');
        $this->logFor($otherCase, 'ai_agent', 'AI:proposal:other:approved',
            'Other case AI history');

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('This case troubleshooting note')
            ->assertDontSee('Other case AI history')
            ->assertDontSee('Unrelated internal comment description');
    }

    public function test_show_older_activity_expands_newest_first_timeline_safely(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        for ($i = 1; $i <= 21; $i++) {
            $this->logFor($case, 'ai_agent', 'AI:analysis:'.$i.':requested',
                sprintf('Historical AI event [%02d]', $i));
        }

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Show older activity')
            ->assertDontSee('Historical AI event [01]')
            ->call('loadMoreTimeline')
            ->assertSet('timelineLimit', 40)
            ->assertSee('Historical AI event [01]')
            ->assertDontSee('Show older activity');
    }

    public function test_open_workflow_survives_polling_and_can_be_closed_deliberately(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSet('expandedWorkflows', [])
            ->call('toggleWorkflow', 'analysis')
            ->assertSet('expandedWorkflows', ['analysis'])
            ->assertSee('aria-expanded="true"', escape: false)
            ->call('refreshAnalysis')
            ->assertSet('expandedWorkflows', ['analysis'])
            ->call('toggleWorkflow', 'analysis')
            ->assertSet('expandedWorkflows', []);
    }

    public function test_multiple_workflows_remain_open_after_polling(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('toggleWorkflow', 'analysis')
            ->call('toggleWorkflow', 'category')
            ->assertSet('expandedWorkflows', ['analysis', 'category'])
            ->call('refreshAnalysis')
            ->assertSet('expandedWorkflows', ['analysis', 'category'])
            ->call('toggleWorkflow', 'not-a-real-workflow')
            ->assertSet('expandedWorkflows', ['analysis', 'category']);
    }

    public function test_event_filters_only_show_matching_case_activity_and_reset_pagination(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->logFor($case, 'ai_agent', 'AI:analysis:101:completed',
            'Analysis filtering marker');
        $this->logFor($case, 'ai_agent', 'AI:proposal:some-id:approved',
            'Category change — Proposal filtering marker');
        $this->logFor($case, 'ai_assisted', 'Reviewed troubleshooting',
            'Reviewed note filtering marker');
        $this->logFor($case, 'ai_agent', 'AI:resolution-notification:101:sent',
            'Customer notification filtering marker');

        $this->actingAs($viewer);
        $component = Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('loadMoreTimeline')
            ->assertSet('timelineLimit', 40)
            ->call('setTimelineFilter', 'approvals')
            ->assertSet('timelineFilter', 'approvals')
            ->assertSet('timelineLimit', 20)
            ->assertSee('Proposal filtering marker')
            ->assertDontSee('Analysis filtering marker')
            ->assertDontSee('Reviewed note filtering marker')
            ->assertDontSee('Customer notification filtering marker');

        $component->call('setTimelineFilter', 'reviews')
            ->assertSee('Reviewed note filtering marker')
            ->assertDontSee('Proposal filtering marker')
            ->call('setTimelineFilter', 'analysis')
            ->assertSee('Analysis filtering marker')
            ->assertDontSee('Proposal filtering marker')
            ->call('setTimelineFilter', 'notifications')
            ->assertSee('Customer notification filtering marker')
            ->assertDontSee('Analysis filtering marker')
            ->call('setTimelineFilter', 'all')
            ->assertSee('Analysis filtering marker')
            ->assertSee('Proposal filtering marker')
            ->assertSee('Reviewed note filtering marker')
            ->assertSee('Customer notification filtering marker');
    }

    public function test_related_workflow_link_opens_panel_without_closing_an_existing_panel(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->logFor($case, 'ai_agent', 'AI:proposal:category:prepared',
            'Category change — Proposed category: Technical Support');

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Open related workflow')
            ->assertSee('href="#ai-workflow-category"', escape: false)
            ->call('toggleWorkflow', 'analysis')
            ->call('openWorkflow', 'category')
            ->assertSet('expandedWorkflows', ['analysis', 'category'])
            ->call('openWorkflow', 'category')
            ->assertSet('expandedWorkflows', ['analysis', 'category'])
            ->call('refreshAnalysis')
            ->assertSet('expandedWorkflows', ['analysis', 'category'])
            ->call('openWorkflow', 'unrecognised')
            ->assertSet('expandedWorkflows', ['analysis', 'category']);
    }

    public function test_invalid_timeline_filter_is_ignored(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('setTimelineFilter', 'nonexistent-filter')
            ->assertSet('timelineFilter', 'all');
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function logFor(tickets $case, string $source, string $summary, string $description): TaskLogs
    {
        return TaskLogs::query()->create([
            'ticket_id' => $case->id,
            'name' => 'AI Agent',
            'short_description' => $summary,
            'description' => $description,
            'type' => 'Internal Update',
            'source' => $source,
            'visible_to_external' => false,
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
            'short_description' => 'Portal troubleshooting case',
            'description' => 'A test case for the AI activity timeline.',
            'contact_email' => 'customer@example.test',
            'contact_telephone' => '0123456789',
        ]);
    }
}
