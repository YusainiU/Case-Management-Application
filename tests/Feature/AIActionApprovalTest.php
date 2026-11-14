<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Livewire\CategoryApprovals;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\CategoryProposalService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIActionApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_analysis_proposes_an_existing_category_without_changing_case(): void
    {
        $user = User::factory()->create();
        $case = $this->caseFor($user);
        $analysis = $this->analysisFor($case);

        $proposal = app(CategoryProposalService::class)->fromAnalysis($analysis);

        $this->assertNotNull($proposal);
        $this->assertSame('pending', $proposal->status);
        $this->assertSame(CaseCategory::TechnicalSupport->value, $proposal->payload['category']);
        $this->assertSame(CaseCategory::Failure, $case->refresh()->category);
        $this->assertSame($proposal->id, app(CategoryProposalService::class)->fromAnalysis($analysis)->id);
    }

    public function test_invalid_model_suggestion_creates_no_proposal(): void
    {
        $user = User::factory()->create();
        $case = $this->caseFor($user);
        $analysis = $this->analysisFor($case, 'not-a-valid-category');

        $this->assertNull(app(CategoryProposalService::class)->fromAnalysis($analysis));
        $this->assertDatabaseCount('ai_action_proposals', 0);
    }

    public function test_manager_approval_queues_action_but_does_not_change_case_synchronously(): void
    {
        config()->set('ai-management.approvals.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertSame(CaseCategory::Failure, $case->refresh()->category);
        $this->assertDatabaseHas('ai_action_approvals', [
            'proposal_id' => $proposal->id,
            'reviewer_id' => $manager->id,
            'decision' => 'approved',
        ]);
        Bus::assertDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_approval_does_not_dispatch_when_execution_is_disabled(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        Bus::fake();
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertSame(CaseCategory::Failure, $case->refresh()->category);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_user_missing_approval_permission_cannot_approve(): void
    {
        $user = User::factory()->create();
        $this->grant($user, 'case.view', 'case.update');
        $case = $this->caseFor($user);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        $this->expectException(AuthorizationException::class);
        app(AIApprovalService::class)->approve($proposal->id, $user);
    }

    public function test_rejection_does_not_queue_execution(): void
    {
        Bus::fake();
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        app(AIApprovalService::class)->reject($proposal->id, $manager, 'Not appropriate');

        $this->assertSame('rejected', $proposal->refresh()->status);
        $this->assertSame(CaseCategory::Failure, $case->refresh()->category);
        $this->assertDatabaseHas('ai_action_approvals', ['proposal_id' => $proposal->id, 'decision' => 'rejected']);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_stale_proposal_cannot_be_approved(): void
    {
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        DB::table($case->getTable())->where('id', $case->id)->update(['category' => CaseCategory::PartReplacement->value]);

        $this->expectException(\DomainException::class);
        app(AIApprovalService::class)->approve($proposal->id, $manager);
    }

    public function test_approved_action_executes_once_and_is_idempotent(): void
    {
        Bus::fake();
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->handle(app(CaseRepository::class));
        $job->handle(app(CaseRepository::class));

        $this->assertSame(CaseCategory::TechnicalSupport, $case->refresh()->category);
        $this->assertSame('completed', $proposal->refresh()->status);
        $this->assertDatabaseCount('ai_action_executions', 1);
    }

    public function test_livewire_prevents_viewer_from_approving(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);
        $proposal = app(CategoryProposalService::class)->fromAnalysis($this->analysisFor($case));

        $this->actingAs($viewer);
        Livewire::test(CategoryApprovals::class, ['case' => $case])
            ->call('approve', $proposal->id)
            ->assertForbidden();
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);
    }

    private function analysisFor(tickets $case, string $suggestion = 'Technical Support'): AIAnalysis
    {
        return AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
            'result' => [
                'summary' => 'Categorise as technical support.',
                'suggested_category' => $suggestion,
            ],
        ]);
    }

    private function caseFor(User $creator): tickets
    {
        $customer = Customer::factory()->create();

        return tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure,
            'created_by' => $creator->id,
            'source' => CaseSource::Phone,
            'short_description' => 'Test case for AI category approval',
            'description' => 'Example fault for validation.',
            'state' => CaseState::Open,
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
