<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\CaseAssistant;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Models\AIActionProposal;
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

    public function test_timeline_groups_entries_by_local_calendar_day_with_newest_day_first(): void
    {
        config()->set('app.timezone', 'Europe/London');

        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $older = $this->logFor($case, 'ai_agent', 'AI:analysis:901:completed', 'Earlier day event');
        $first = $this->logFor($case, 'ai_agent', 'AI:analysis:902:completed', 'First event on newer day');
        $second = $this->logFor($case, 'ai_agent', 'AI:analysis:903:completed', 'Latest event on newer day');

        DB::table('task_logs')->where('id', $older->id)
            ->update(['created_at' => '2026-11-10 23:40:00']);
        DB::table('task_logs')->where('id', $first->id)
            ->update(['created_at' => '2026-11-11 00:02:00']);
        DB::table('task_logs')->where('id', $second->id)
            ->update(['created_at' => '2026-11-11 18:25:32']);

        $this->actingAs($viewer);
        $component = Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSeeInOrder([
                'Wednesday, 11 November 2026',
                'Latest event on newer day',
                'First event on newer day',
                'Tuesday, 10 November 2026',
                'Earlier day event',
            ])
            ->assertSee('18:25:32')
            ->assertSee('00:02:00')
            ->assertSee('23:40:00');

        $this->assertSame(1, substr_count($component->html(), 'Wednesday, 11 November 2026'));
        $this->assertSame(1, substr_count($component->html(), 'Tuesday, 10 November 2026'));
    }

    public function test_date_headings_follow_timeline_filter_without_leaking_other_event_days(): void
    {
        config()->set('app.timezone', 'Europe/London');

        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $analysis = $this->logFor($case, 'ai_agent', 'AI:analysis:1001:completed', 'Analysis day only');
        $notification = $this->logFor($case, 'ai_agent', 'AI:resolution-notification:1001:sent',
            'Notification day only');

        DB::table('task_logs')->where('id', $analysis->id)
            ->update(['created_at' => '2026-11-10 12:00:00']);
        DB::table('task_logs')->where('id', $notification->id)
            ->update(['created_at' => '2026-11-11 12:00:00']);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('setTimelineFilter', 'analysis')
            ->assertSee('Tuesday, 10 November 2026')
            ->assertDontSee('Wednesday, 11 November 2026')
            ->call('setTimelineFilter', 'notifications')
            ->assertSee('Wednesday, 11 November 2026')
            ->assertDontSee('Tuesday, 10 November 2026');
    }

    public function test_analyze_button_exposes_a_spinner_and_indeterminate_progress_status(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view', 'ai.case_assistant.analyze');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Queuing analysis…')
            ->assertSee('Starting Case Assistant analysis…')
            ->assertSee('wire:loading.inline-flex', escape: false);

        AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'pending',
        ]);

        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Analysis queued; waiting for a worker.')
            ->assertSee('Case Assistant analysis in progress');

        AIAnalysis::query()->where('case_id', $case->id)
            ->update(['status' => 'processing']);

        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('AI provider is processing the case.');
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

    public function test_needs_attention_includes_only_current_valid_pending_approvals(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $otherCase = $this->caseFor($viewer);

        $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());
        $this->proposalFor($case, 'assign_case_engineer', 'pending', now()->subHour());
        $this->proposalFor($case, 'start_case_work', 'completed', now()->addHour());
        $this->proposalFor($otherCase, 'assign_case_engineer', 'pending', now()->addHour());

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Needs attention')
            ->assertSee('Category change awaiting review')
            ->assertDontSee('Engineer assignment awaiting review')
            ->assertDontSee('Start Work awaiting review')
            ->assertSee('href="#ai-workflow-category"', escape: false);
    }

    public function test_needs_attention_reports_latest_analysis_and_email_failure_without_old_history(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'failed',
        ]);

        DB::table('ai_resolution_notifications')->insert([
            'case_id' => $case->id,
            'status' => 'failed',
            'attempts' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer);
        $component = Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Latest Case Assistant analysis failed')
            ->assertSee('Customer resolution email failed')
            ->assertSee('Review workflow');

        AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
        ]);
        DB::table('ai_resolution_notifications')->where('case_id', $case->id)
            ->update(['status' => 'sent']);

        $component->call('refreshAnalysis')
            ->assertDontSee('Latest Case Assistant analysis failed')
            ->assertDontSee('Customer resolution email failed')
            ->assertSee('No outstanding AI approvals or recorded failures requiring review.');
    }

    public function test_needs_attention_includes_failed_execution_but_not_successful_execution(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $failed = $this->proposalFor($case, 'start_case_work', 'failed', now()->subHour());
        $completed = $this->proposalFor($case, 'assign_case_engineer', 'completed', now()->subHour());

        foreach ([$failed->id => 'failed', $completed->id => 'completed'] as $id => $status) {
            DB::table('ai_action_executions')->insert([
                'proposal_id' => $id,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Start Work execution failed')
            ->assertDontSee('Engineer assignment execution failed')
            ->assertSee('href="#ai-workflow-start-work"', escape: false);
    }

    public function test_attention_is_empty_for_unrelated_case_records(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $other = $this->caseFor($viewer);

        $this->proposalFor($other, 'update_case_category', 'pending', now()->addHour());
        DB::table('ai_resolution_notifications')->insert([
            'case_id' => $other->id,
            'status' => 'skipped',
            'attempts' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('No outstanding AI approvals or recorded failures requiring review.')
            ->assertDontSee('Category change awaiting review')
            ->assertDontSee('Customer resolution email skipped');
    }

    public function test_workflow_overview_shows_recorded_stages_without_claiming_resolution(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
        ]);
        $this->logFor($case, 'ai_assisted', 'Reviewed troubleshooting',
            'Work completed and tested by staff.');
        $this->logFor($case, 'ai_resolution_review', 'Resolution readiness handoff',
            'A handoff has been saved, but this case remains open.');

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Case workflow overview')
            ->assertSee('Latest analysis completed')
            ->assertSee('Reviewed internal note saved')
            ->assertSee('Handoff saved; case not resolved')
            ->assertSee('No engineer assigned')
            ->assertSee('Case remains Open')
            ->assertSee('href="#ai-workflow-resolution"', escape: false);

        $this->assertSame(CaseState::Open, $case->refresh()->state);
    }

    public function test_completed_initial_analysis_makes_troubleshooting_stage_ready_without_rerun(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $analysis = AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
        ]);

        DB::table('ai_analyses')->where('id', $analysis->id)
            ->update(['updated_at' => now()->subHour()]);
        DB::table('tickets')->where('id', $case->id)
            ->update(['state' => CaseState::In_Progress->value, 'updated_at' => now()]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Ready for review')
            ->assertSee('Ready to review the completed analysis; no re-analysis required')
            ->assertSee('href="#ai-workflow-troubleshoot"', escape: false);
    }

    public function test_workflow_overview_reflects_latest_pending_approval_and_state_change(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->proposalFor($case, 'update_case_category', 'completed', now()->subDay());
        $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());

        $this->actingAs($viewer);
        $component = Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Awaiting category approval')
            ->assertSee('Case remains Open');

        DB::table('tickets')->where('id', $case->id)->update([
            'state' => CaseState::Resolved->value,
            'resolved_datetime' => now()->timestamp,
        ]);

        $component->call('refreshAnalysis')
            ->assertSee('Case is resolved or completed')
            ->assertDontSee('Handoff saved; case not resolved');
    }

    public function test_workflow_overview_prioritizes_active_approval_when_proposal_timestamps_tie(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $completed = $this->proposalFor($case, 'update_case_category', 'completed', now()->addHour());
        $pending = $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());

        // The production MySQL schema can store both created_at values at
        // identical second precision. The UUID primary key is not a
        // chronological tiebreaker.
        DB::table('ai_action_proposals')->whereIn('id', [$completed->id, $pending->id])
            ->update(['created_at' => '2026-11-15 12:00:00']);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Awaiting category approval')
            ->assertSee('Needs attention');

        DB::table('ai_action_proposals')->where('id', $pending->id)
            ->update(['expires_at' => now()->subMinute()]);

        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertDontSee('Awaiting category approval');
    }

    public function test_workflow_overview_is_scoped_to_current_case_and_links_open_correct_panels(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $other = $this->caseFor($viewer);

        $this->logFor($other, 'ai_assisted', 'Other case review', 'Not for this case');
        $this->proposalFor($other, 'assign_case_engineer', 'pending', now()->addHour());

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('No reviewed troubleshooting note saved')
            ->assertSee('No engineer assigned')
            ->assertDontSee('Awaiting assignment approval')
            ->call('openWorkflow', 'troubleshoot')
            ->assertSet('expandedWorkflows', ['troubleshoot'])
            ->call('refreshAnalysis')
            ->assertSet('expandedWorkflows', ['troubleshoot']);
    }

    public function test_suggested_next_step_points_to_initial_analysis_when_no_analysis_exists(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Suggested next step')
            ->assertSee('Review case analysis')
            ->assertSee('Open suggested workflow')
            ->assertSee('href="#ai-workflow-analysis"', escape: false);
    }

    public function test_suggested_next_step_prioritises_pending_approval_over_regular_workflow(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Review an awaiting approval')
            ->assertSee('href="#ai-workflow-category"', escape: false)
            ->call('openWorkflow', 'category')
            ->assertSet('expandedWorkflows', ['category']);

        $this->assertSame(CaseState::Open, $case->refresh()->state);
    }

    public function test_suggested_next_step_prioritises_notification_failure_and_never_resends(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());

        DB::table('ai_resolution_notifications')->insert([
            'case_id' => $case->id,
            'status' => 'failed',
            'attempts' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Review an outstanding issue')
            ->assertSee('Customer resolution email failed')
            ->assertSee('href="#ai-workflow-resolution"', escape: false)
            ->call('openWorkflow', 'resolution')
            ->assertSet('expandedWorkflows', ['resolution']);

        $this->assertDatabaseHas('ai_resolution_notifications', [
            'case_id' => $case->id,
            'status' => 'failed',
            'attempts' => 3,
        ]);
    }

    public function test_suggested_next_step_uses_existing_analysis_for_troubleshooting(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
        ]);
        DB::table('tickets')->where('id', $case->id)->update([
            'assigned_to' => $viewer->id,
            'state' => CaseState::In_Progress->value,
        ]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Review troubleshooting suggestions')
            ->assertSee('No re-analysis is needed')
            ->assertSee('href="#ai-workflow-troubleshoot"', escape: false);
    }

    public function test_resolved_case_does_not_suggest_approving_an_old_pending_proposal(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->proposalFor($case, 'update_case_category', 'pending', now()->addHour());

        DB::table('tickets')->where('id', $case->id)->update([
            'state' => CaseState::Resolved->value,
            'resolved_datetime' => now()->timestamp,
        ]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Resolution recorded')
            ->assertDontSee('Review an awaiting approval')
            ->assertSee('href="#ai-workflow-resolution"', escape: false);
    }

    public function test_suggested_next_step_for_resolved_case_is_review_only(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        DB::table('tickets')->where('id', $case->id)->update([
            'state' => CaseState::Resolved->value,
            'resolved_datetime' => now()->timestamp,
        ]);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('Resolution recorded')
            ->assertSee('no further AI action is automatically required')
            ->assertSee('href="#ai-workflow-resolution"', escape: false);
    }

    public function test_focus_view_hides_long_sections_but_keeps_actions_and_attention_visible(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->logFor($case, 'ai_agent', 'AI:analysis:123:requested',
            'Historical event only shown in full timeline');

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSet('showOverview', true)
            ->assertSet('showTimeline', true)
            ->assertSee('Historical event only shown in full timeline')
            ->call('setFocusView', true)
            ->assertSet('showOverview', false)
            ->assertSet('showTimeline', false)
            ->assertSee('Suggested next step')
            ->assertSee('Needs attention')
            ->assertSee('Actions and reviews')
            ->assertSee('Show overview')
            ->assertSee('Show timeline')
            ->assertDontSee('Historical event only shown in full timeline')
            ->call('refreshAnalysis')
            ->assertSet('showOverview', false)
            ->assertSet('showTimeline', false)
            ->call('setFocusView', false)
            ->assertSet('showOverview', true)
            ->assertSet('showTimeline', true)
            ->assertSee('Historical event only shown in full timeline');
    }

    public function test_independent_section_toggles_preserve_filter_and_open_workflows(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $this->logFor($case, 'ai_agent', 'AI:analysis:123:completed',
            'Analysis history visible when timeline is shown');

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('setTimelineFilter', 'analysis')
            ->call('toggleWorkflow', 'analysis')
            ->call('toggleOverview')
            ->assertSet('showOverview', false)
            ->assertSet('showTimeline', true)
            ->assertSee('Analysis history visible when timeline is shown')
            ->call('toggleTimeline')
            ->assertSet('showTimeline', false)
            ->assertSet('timelineFilter', 'analysis')
            ->assertSet('expandedWorkflows', ['analysis'])
            ->call('refreshAnalysis')
            ->assertSet('showOverview', false)
            ->assertSet('showTimeline', false)
            ->call('toggleTimeline')
            ->assertSet('showTimeline', true)
            ->assertSet('timelineFilter', 'analysis')
            ->assertSee('Analysis history visible when timeline is shown')
            ->call('toggleOverview')
            ->assertSet('showOverview', true)
            ->assertSet('expandedWorkflows', ['analysis']);
    }

    public function test_focus_view_does_not_change_case_or_trigger_workflow_actions(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->call('setFocusView', true)
            ->call('toggleOverview')
            ->call('toggleTimeline')
            ->call('setFocusView', false)
            ->assertHasNoErrors();

        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertNull($case->resolved_datetime);
        $this->assertDatabaseMissing('ai_action_proposals', ['case_id' => $case->id]);
    }

    private function proposalFor(tickets $case, string $action, string $status, $expiresAt): AIActionProposal
    {
        return AIActionProposal::query()->create([
            'case_id' => $case->id,
            'action_type' => $action,
            'payload' => [],
            'source_hash' => str_repeat('a', 64),
            'payload_hash' => str_repeat('b', 64),
            'reason' => 'Test attention state only.',
            'status' => $status,
            'expires_at' => $expiresAt,
        ]);
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
