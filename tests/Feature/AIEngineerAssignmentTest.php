<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Livewire\EngineerAssignmentApprovals;
use Simplydigital\AIManagement\Livewire\ApprovalReviewQueue;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\EngineerAssignmentProposalService;
use Simplydigital\AIManagement\Services\EngineerRecommendationService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIEngineerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_rankings_prefer_skill_matches_then_lower_open_case_count(): void
    {
        $owner = User::factory()->create();
        $matching = User::factory()->create();
        $other = User::factory()->create();
        $this->engineer($matching, ['Email']);
        $this->engineer($other, ['Hardware']);
        $case = $this->caseFor($owner);

        // Even with a higher workload, a documented skill match ranks first.
        $this->caseFor($owner, $matching->id);
        $ranked = app(EngineerRecommendationService::class)->recommend($case);

        $this->assertSame($matching->id, $ranked[0]['user_id']);
        $this->assertSame(['Email'], $ranked[0]['matched_skills']);
        $this->assertSame(1, $ranked[0]['open_cases']);
        $this->assertSame('workload_only', $ranked[1]['evidence']);
    }

    public function test_valid_case_matched_certification_ranks_ahead_of_workload_only(): void
    {
        $owner = User::factory()->create();
        $certified = User::factory()->create();
        $uncertified = User::factory()->create();
        $certifiedId = $this->engineer($certified);
        $this->engineer($uncertified);
        $this->certification($certifiedId, 'Email', now()->addMonth());

        // Certificate match outranks workload, despite an extra open case.
        $this->caseFor($owner, $certified->id);
        $ranked = app(EngineerRecommendationService::class)->recommend($this->caseFor($owner));

        $this->assertSame($certified->id, $ranked[0]['user_id']);
        $this->assertSame(['Email'], $ranked[0]['matched_certifications']);
        $this->assertSame('certification_match', $ranked[0]['evidence']);
        $this->assertSame('workload_only', $ranked[1]['evidence']);

        $proposal = app(EngineerAssignmentProposalService::class)
            ->propose($this->caseFor($owner), $certified->id);
        $this->assertStringContainsString(
            'Matched active, unexpired certifications: Email',
            $proposal->reason,
        );
    }

    public function test_expired_inactive_and_undated_certifications_provide_no_matching_evidence(): void
    {
        $owner = User::factory()->create();
        $expired = User::factory()->create();
        $inactive = User::factory()->create();
        $undated = User::factory()->create();
        $this->certification($this->engineer($expired), 'Email', now()->subDay());
        $this->certification($this->engineer($inactive), 'Email', now()->addMonth(), false);
        $this->certification($this->engineer($undated), 'Email', null);

        $ranked = app(EngineerRecommendationService::class)->recommend($this->caseFor($owner));
        foreach ($ranked as $candidate) {
            $this->assertSame([], $candidate['matched_certifications']);
            $this->assertSame('workload_only', $candidate['evidence']);
        }
    }

    public function test_skill_match_outweighs_certification_match(): void
    {
        $owner = User::factory()->create();
        $skilled = User::factory()->create();
        $certified = User::factory()->create();
        $this->engineer($skilled, ['Email']);
        $this->certification($this->engineer($certified), 'Email', now()->addMonth());

        $ranked = app(EngineerRecommendationService::class)->recommend($this->caseFor($owner));
        $this->assertSame($skilled->id, $ranked[0]['user_id']);
        $this->assertSame($certified->id, $ranked[1]['user_id']);
    }

    public function test_engineer_assignment_appears_in_shared_review_queue(): void
    {
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.view', 'ai.proposals.approve');
        $this->engineer($engineer, ['Email']);
        $case = $this->caseFor($manager);
        app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        $this->actingAs($manager);
        Livewire::test(ApprovalReviewQueue::class)
            ->assertSee('Engineer assignment')
            ->assertSee($engineer->name)
            ->assertSee('Current assignee')
            ->assertSee('Proposed engineer');
    }

    public function test_inactive_engineers_are_excluded_from_recommendations(): void
    {
        $owner = User::factory()->create();
        $active = User::factory()->create();
        $inactive = User::factory()->create();
        $this->engineer($active);
        $this->engineer($inactive, [], false);

        $ids = array_column(app(EngineerRecommendationService::class)->recommend($this->caseFor($owner)), 'user_id');

        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_proposal_and_approval_recorded_without_changing_assignee_when_execution_disabled(): void
    {
        // Disable the action-specific flag regardless of the developer's .env.
        config()->set('ai-management.approvals.execution_enabled', false);
        config()->set('ai-management.engineer_assignment.execution_enabled', false);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve', 'ai.case_assistant.analyze');
        $this->engineer($engineer, ['Email']);
        $case = $this->caseFor($manager);

        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);
        $this->assertSame('assign_case_engineer', $proposal->action_type);
        $this->assertSame('pending', $proposal->status);
        $this->assertNull($case->refresh()->assigned_to);

        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertNull($case->refresh()->assigned_to);
        $this->assertDatabaseHas('ai_action_approvals', [
            'proposal_id' => $proposal->id,
            'reviewer_id' => $manager->id,
            'decision' => 'approved',
        ]);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_category_execution_flag_does_not_enable_engineer_execution(): void
    {
        config()->set('ai-management.approvals.execution_enabled', true);
        config()->set('ai-management.engineer_assignment.execution_enabled', false);
        Bus::fake();

        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        app(AIApprovalService::class)->approve($proposal->id, $manager);
        (new ExecuteApprovedAIAction($proposal->id))->handle(app(CaseRepository::class));

        $this->assertSame('approved', $proposal->refresh()->status);
        $this->assertNull($case->refresh()->assigned_to);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_manager_without_approval_permission_cannot_approve_engineer_change(): void
    {
        $user = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($user, 'case.view', 'case.update', 'ai.case_assistant.analyze');
        $this->engineer($engineer);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($this->caseFor($user), $engineer->id);

        $this->expectException(AuthorizationException::class);
        app(AIApprovalService::class)->approve($proposal->id, $user);
    }

    public function test_case_reassignment_makes_engineer_proposal_stale(): void
    {
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $other = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        DB::table('tickets')->where('id', $case->id)->update(['assigned_to' => $other->id]);

        $this->expectException(\DomainException::class);
        app(AIApprovalService::class)->approve($proposal->id, $manager);
    }

    public function test_inactive_engineer_cannot_be_approved(): void
    {
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($this->caseFor($manager), $engineer->id);

        DB::table('engineer_management')->where('user_id', $engineer->id)->update(['active' => false]);

        $this->expectException(\DomainException::class);
        app(AIApprovalService::class)->approve($proposal->id, $manager);
    }

    public function test_approved_engineer_assignment_executes_idempotently_with_category_execution_disabled(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        config()->set('ai-management.engineer_assignment.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        app(AIApprovalService::class)->approve($proposal->id, $manager);
        Bus::assertDispatched(ExecuteApprovedAIAction::class);
        $this->assertNull($case->refresh()->assigned_to);
        $this->assertDatabaseHas('ai_action_executions', [
            'proposal_id' => $proposal->id,
            'status' => 'queued',
        ]);
        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->handle(app(CaseRepository::class));
        $job->handle(app(CaseRepository::class));

        $this->assertSame($engineer->id, $case->refresh()->assigned_to);
        $this->assertSame('completed', $proposal->refresh()->status);
        $this->assertDatabaseCount('ai_action_executions', 1);
    }

    public function test_exhausted_queue_failure_is_audited_and_cannot_execute_again(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        config()->set('ai-management.engineer_assignment.execution_enabled', true);
        Bus::fake();

        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve', 'ai.proposals.view');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);
        app(AIApprovalService::class)->approve($proposal->id, $manager);

        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->failed(new \RuntimeException('Secret runtime details must not be shown'));

        $this->assertSame('failed', $proposal->refresh()->status);
        $execution = DB::table('ai_action_executions')->where('proposal_id', $proposal->id)->first();
        $this->assertSame('failed', $execution->status);
        $this->assertStringNotContainsString('Secret runtime details', $execution->error);
        $job->handle(app(CaseRepository::class));
        $this->assertNull($case->refresh()->assigned_to);
        $this->assertSame(3, $job->tries);

        $this->actingAs($manager);
        Livewire::test(ApprovalReviewQueue::class)
            ->set('status', 'failed')
            ->assertSee('Execution failed after queue retries')
            ->assertDontSee('Secret runtime details');
    }

    public function test_changed_assignment_marks_queued_execution_as_stale(): void
    {
        config()->set('ai-management.approvals.execution_enabled', false);
        config()->set('ai-management.engineer_assignment.execution_enabled', true);
        Bus::fake();

        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $newAssignee = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);
        app(AIApprovalService::class)->approve($proposal->id, $manager);

        DB::table('tickets')->where('id', $case->id)->update(['assigned_to' => $newAssignee->id]);
        (new ExecuteApprovedAIAction($proposal->id))->handle(app(CaseRepository::class));

        $this->assertSame('stale', $proposal->refresh()->status);
        $this->assertSame($newAssignee->id, $case->refresh()->assigned_to);
        $this->assertDatabaseHas('ai_action_executions', [
            'proposal_id' => $proposal->id,
            'status' => 'stale',
        ]);
    }

    public function test_viewer_cannot_prepare_or_approve_assignment(): void
    {
        $viewer = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $this->engineer($engineer);
        $case = $this->caseFor($viewer);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        $this->actingAs($viewer);
        Livewire::test(EngineerAssignmentApprovals::class, ['case' => $case])
            ->set('selectedEngineerId', $engineer->id)
            ->call('prepare')
            ->assertForbidden();

        Livewire::test(EngineerAssignmentApprovals::class, ['case' => $case])
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

    private function engineer(User $user, array $skills = [], bool $active = true): int
    {
        $id = DB::table('engineer_management')->insertGetId([
            'number' => 'ENG-'.uniqid(),
            'user_id' => $user->id,
            'position' => 'Engineer',
            'active' => $active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($skills as $label) {
            $skillId = DB::table('skills')->insertGetId([
                'number' => 'SKL-'.uniqid(),
                'skills' => $label,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('engineer_skills')->insert([
                'number' => 'ES-'.uniqid(),
                'engineer_id' => $id,
                'skill_id' => $skillId,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $id;
    }

    private function certification(int $engineerId, string $name, ?\DateTimeInterface $expiry, bool $active = true): void
    {
        $certificateId = DB::table('certificates')->insertGetId([
            'number' => 'CERT-'.uniqid(),
            'name' => $name,
            'type' => 'Professional',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('engineer_certifications')->insert([
            'certification_number' => 'ENGCERT-'.uniqid(),
            'engineer_id' => $engineerId,
            'certificate_id' => $certificateId,
            'active' => $active,
            'expired' => false,
            'expiry_date' => $expiry,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function caseFor(User $creator, ?int $assignedTo = null): tickets
    {
        $customer = Customer::factory()->create();

        return tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::TechnicalSupport,
            'created_by' => $creator->id,
            'source' => CaseSource::Phone,
            'short_description' => 'Email account setup',
            'description' => 'Help with Email configuration on a laptop.',
            'state' => CaseState::Open,
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
            'assigned_to' => $assignedTo,
        ]);
    }
}
