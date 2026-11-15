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
        config()->set('ai-management.approvals.execution_enabled', false);
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

    public function test_approved_engineer_assignment_executes_idempotently_when_explicitly_enabled(): void
    {
        config()->set('ai-management.approvals.execution_enabled', true);
        config()->set('ai-management.engineer_assignment.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $this->engineer($engineer);
        $case = $this->caseFor($manager);
        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $engineer->id);

        app(AIApprovalService::class)->approve($proposal->id, $manager);
        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->handle(app(CaseRepository::class));
        $job->handle(app(CaseRepository::class));

        $this->assertSame($engineer->id, $case->refresh()->assigned_to);
        $this->assertSame('completed', $proposal->refresh()->status);
        $this->assertDatabaseCount('ai_action_executions', 1);
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

    private function engineer(User $user, array $skills = [], bool $active = true): void
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
