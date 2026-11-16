<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Livewire\ApprovalReviewQueue;
use Simplydigital\AIManagement\Livewire\CaseWorkStartApprovals;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\CaseWorkStartProposalService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIWorkStartApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_open_case_can_prepare_work_start_in_shared_queue(): void
    {
        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.view', 'ai.proposals.approve', 'ai.case_assistant.analyze');
        $case = $this->caseFor($manager, $assignee->id);

        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        $this->assertSame('pending', $proposal->status);
        $this->assertSame('start_case_work', $proposal->action_type);
        $this->assertSame(['state' => CaseState::In_Progress->value], $proposal->payload);
        $this->assertSame(CaseState::Open, $case->refresh()->state);

        $this->actingAs($manager);
        Livewire::test(ApprovalReviewQueue::class)
            ->assertSee('Start Work')
            ->assertSee('Proposed case state')
            ->assertSee('In Progress');
    }

    public function test_unassigned_case_cannot_prepare_work_start(): void
    {
        $manager = User::factory()->create();
        $this->expectException(\DomainException::class);
        app(CaseWorkStartProposalService::class)->propose($this->caseFor($manager));
    }

    public function test_disabled_work_start_execution_does_not_change_case_or_queue_job(): void
    {
        config()->set('ai-management.approvals.execution_enabled', true);
        config()->set('ai-management.engineer_assignment.execution_enabled', true);
        config()->set('ai-management.work_start.execution_enabled', false);
        Bus::fake();

        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_work_start_executes_once_when_explicitly_enabled(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        config()->set('ai-management.engineer_assignment.execution_enabled', false);
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();

        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        app(AIApprovalService::class)->approve($proposal->id, $manager);
        Bus::assertDispatched(ExecuteApprovedAIAction::class);
        $this->assertDatabaseHas('ai_action_executions', [
            'proposal_id' => $proposal->id,
            'status' => 'queued',
        ]);

        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->handle(app(CaseRepository::class));
        $job->handle(app(CaseRepository::class));

        $this->assertSame(CaseState::In_Progress, $case->refresh()->state);
        $this->assertSame('completed', $proposal->refresh()->status);
        $this->assertDatabaseCount('ai_action_executions', 1);
        $this->assertNull($case->resolved_datetime);
        $this->assertNull($case->closed_datetime);
    }

    public function test_approval_rejected_when_case_changed_since_proposal(): void
    {
        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        $case->update(['state' => CaseState::Paused]);

        $this->expectException(\DomainException::class);
        app(AIApprovalService::class)->approve($proposal->id, $manager);
    }

    public function test_rejecting_work_start_from_shared_queue_preserves_open_state(): void
    {
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.view', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        $this->actingAs($manager);
        Livewire::test(ApprovalReviewQueue::class)
            ->call('reject', $proposal->id)
            ->assertSee('Proposal rejected');

        $this->assertSame('rejected', $proposal->refresh()->status);
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_change_after_approval_prevents_execution(): void
    {
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $assignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        app(AIApprovalService::class)->approve($proposal->id, $manager);

        DB::table('tickets')->where('id', $case->id)->update(['assigned_to' => $newAssignee->id]);
        (new ExecuteApprovedAIAction($proposal->id))->handle(app(CaseRepository::class));

        $this->assertSame('stale', $proposal->refresh()->status);
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertDatabaseHas('ai_action_executions', [
            'proposal_id' => $proposal->id,
            'status' => 'stale',
        ]);
    }

    public function test_viewer_cannot_propose_or_approve_start_work(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer, $assignee->id);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        $this->actingAs($viewer);
        Livewire::test(CaseWorkStartApprovals::class, ['case' => $case])
            ->call('prepare')->assertForbidden();
        Livewire::test(CaseWorkStartApprovals::class, ['case' => $case])
            ->call('approve', $proposal->id)->assertForbidden();
    }

    public function test_approval_requires_manager_permission(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $this->grant($user, 'case.view', 'case.update', 'ai.case_assistant.analyze');
        $proposal = app(CaseWorkStartProposalService::class)
            ->propose($this->caseFor($user, $assignee->id));

        $this->expectException(AuthorizationException::class);
        app(AIApprovalService::class)->approve($proposal->id, $user);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function caseFor(User $creator, ?int $assigneeId = null): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $creator->id,
            'assigned_to' => $assigneeId,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Portal login HTTP 500',
            'description' => 'Customer reports a server error when signing in.',
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
