<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Livewire\ApprovalReviewQueue;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\CategoryProposalService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIApprovalReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_queue_requires_explicit_view_permission(): void
    {
        $user = User::factory()->create();
        $this->grant($user, 'case.view');
        $this->actingAs($user);

        $this->get(route('ai.approvals.index'))->assertForbidden();
        Livewire::test(ApprovalReviewQueue::class)->assertForbidden();
    }

    public function test_authorised_reader_sees_proposals_and_audit_without_approval_controls(): void
    {
        $manager = User::factory()->create();
        $reader = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->grant($reader, 'case.view', 'ai.proposals.view');

        $case = $this->caseFor($manager);
        $proposal = $this->proposalFor($case);
        app(AIApprovalService::class)->reject($proposal->id, $manager, 'Incorrect classification');

        $this->actingAs($reader);

        $this->get(route('ai.approvals.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('AI Approval Review Queue')
            ->assertSee('Incorrect classification');

        Livewire::test(ApprovalReviewQueue::class)
            ->set('status', 'rejected')
            ->assertSee($case->number)
            ->assertSee('Incorrect classification')
            ->assertSee($manager->name)
            ->assertDontSee('Optional rejection reason')
            ->call('approve', $proposal->id)
            ->assertForbidden();
    }

    public function test_reviewer_can_approve_from_queue_without_executing_change_when_disabled(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        Bus::fake();

        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.view', 'ai.proposals.approve');

        $case = $this->caseFor($manager);
        $proposal = $this->proposalFor($case);
        $this->actingAs($manager);

        Livewire::test(ApprovalReviewQueue::class)
            ->assertSee($case->number)
            ->assertSee('Technical Support')
            ->call('approve', $proposal->id)
            ->assertHasNoErrors()
            ->assertSee('Proposal approved and recorded');

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertSame(CaseCategory::Failure, $case->refresh()->category);
        $this->assertDatabaseHas('ai_action_approvals', [
            'proposal_id' => $proposal->id,
            'decision' => 'approved',
            'reviewer_id' => $manager->id,
        ]);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_review_queue_filter_shows_only_selected_status(): void
    {
        $manager = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.view', 'ai.proposals.approve');
        $first = $this->caseFor($manager);
        $second = $this->caseFor($manager);
        $pending = $this->proposalFor($first);
        $rejected = $this->proposalFor($second);
        app(AIApprovalService::class)->reject($rejected->id, $manager, 'Test rejection');

        $this->actingAs($manager);

        Livewire::test(ApprovalReviewQueue::class)
            ->assertSee($pending->id, false)
            ->assertDontSee('Test rejection')
            ->set('status', 'rejected')
            ->assertSee('Test rejection')
            ->assertDontSee('No proposals match this filter');
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);
    }

    private function proposalFor(tickets $case): AIActionProposal
    {
        $analysis = AIAnalysis::query()->create([
            'case_id' => $case->id,
            'type' => 'case_assistant',
            'status' => 'completed',
            'result' => [
                'summary' => 'Customer needs technical guidance, not fault repair.',
                'suggested_category' => CaseCategory::TechnicalSupport->value,
            ],
        ]);

        return app(CategoryProposalService::class)->fromAnalysis($analysis);
    }

    private function caseFor(User $creator): tickets
    {
        $customer = Customer::factory()->create();

        return tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure,
            'created_by' => $creator->id,
            'source' => CaseSource::Phone,
            'short_description' => 'AI review queue test '.uniqid(),
            'description' => 'Customer asks for help setting up a working email account.',
            'state' => CaseState::Open,
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
