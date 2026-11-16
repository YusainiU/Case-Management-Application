<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\AIProgressLogger;
use Simplydigital\AIManagement\Services\CaseWorkStartProposalService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Case\CaseProgressLog;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIProgressLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_proposal_approval_and_execution_are_logged_once_in_case_progress(): void
    {
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $engineer);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);

        $this->assertLog($case, $proposal->id, 'prepared');
        app(AIApprovalService::class)->approve($proposal->id, $manager);
        $this->assertLog($case, $proposal->id, 'approved');
        $this->assertLog($case, $proposal->id, 'queued');

        $job = new ExecuteApprovedAIAction($proposal->id);
        $job->handle(app(CaseRepository::class));
        $job->handle(app(CaseRepository::class));

        $this->assertLog($case, $proposal->id, 'completed');
        $this->assertSame(4, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());
        $log = TaskLogs::query()->where('ticket_id', $case->id)
            ->where('short_description', 'AI:proposal:'.$proposal->id.':completed')->firstOrFail();
        $this->assertSame('AI Agent', $log->type);
        $this->assertFalse((bool) $log->visible_to_external);
        $this->assertFalse((bool) $log->require_attention);
        $this->assertSame(CaseState::In_Progress, $case->refresh()->state);
    }

    public function test_rejected_proposal_creates_private_log_without_execution(): void
    {
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $engineer);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        app(AIApprovalService::class)->reject($proposal->id, $manager, 'Not yet started');

        $this->assertLog($case, $proposal->id, 'rejected');
        $this->assertSame(2, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());
        $this->assertSame(CaseState::Open, $case->refresh()->state);
        Bus::assertNotDispatched(ExecuteApprovedAIAction::class);
    }

    public function test_exhausted_execution_failure_is_logged_without_exception_details(): void
    {
        config()->set('ai-management.work_start.execution_enabled', true);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $engineer);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        app(AIApprovalService::class)->approve($proposal->id, $manager);

        (new ExecuteApprovedAIAction($proposal->id))->failed(new \RuntimeException('private provider credential'));

        $this->assertLog($case, $proposal->id, 'failed');
        $logs = TaskLogs::query()->where('ticket_id', $case->id)->where('source', 'ai_agent')->get();
        $this->assertStringNotContainsString('private provider credential', $logs->pluck('description')->implode(' '));
        $this->assertSame(CaseState::Open, $case->refresh()->state);
    }

    public function test_analysis_progress_events_are_idempotent(): void
    {
        $manager = User::factory()->create();
        $case = $this->caseFor($manager);
        $analysis = AIAnalysis::query()->create([
            'case_id' => $case->id,
            'user_id' => $manager->id,
            'type' => 'case_assistant',
            'status' => 'pending',
        ]);
        $logger = app(AIProgressLogger::class);
        $logger->analysis($analysis, 'requested');
        $logger->analysis($analysis, 'completed');
        $logger->analysis($analysis, 'completed');

        $this->assertDatabaseHas('task_logs', [
            'ticket_id' => $case->id,
            'short_description' => 'AI:analysis:'.$analysis->id.':requested',
            'source' => 'ai_agent',
        ]);
        $this->assertSame(2, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());
    }

    public function test_historical_audit_backfill_is_opt_in_and_idempotent(): void
    {
        config()->set('ai-management.work_start.execution_enabled', false);
        Bus::fake();
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update', 'ai.proposals.approve');
        $case = $this->caseFor($manager, $engineer);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        app(AIApprovalService::class)->approve($proposal->id, $manager);

        TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->forceDelete();
        $this->assertSame(0, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());

        Artisan::call('ai:backfill-case-progress');
        $this->assertSame(0, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());

        Artisan::call('ai:backfill-case-progress', ['--write' => true]);
        $this->assertLog($case, $proposal->id, 'prepared');
        $this->assertLog($case, $proposal->id, 'approved');
        $this->assertSame(2, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());

        Artisan::call('ai:backfill-case-progress', ['--write' => true]);
        $this->assertSame(2, TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->count());
    }

    public function test_ai_progress_entries_cannot_be_deleted_or_edited_from_progress_component(): void
    {
        $manager = User::factory()->create();
        $engineer = User::factory()->create();
        $this->grant($manager, 'case.view', 'case.update');
        $case = $this->caseFor($manager, $engineer);
        $proposal = app(CaseWorkStartProposalService::class)->propose($case);
        $log = TaskLogs::query()->where('ticket_id', $case->id)
            ->where('short_description', 'AI:proposal:'.$proposal->id.':prepared')->firstOrFail();

        $this->actingAs($manager);
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->call('deleteLog', $log->id)
            ->assertForbidden();
        Livewire::test(CaseProgressLog::class, ['case' => $case])
            ->call('editLog', $log->id)
            ->assertForbidden();
        $this->assertDatabaseHas('task_logs', ['id' => $log->id, 'deleted_at' => null]);
    }

    public function test_engineer_proposal_audit_describes_engineer_by_name(): void
    {
        $manager = User::factory()->create();
        $engineer = User::factory()->create(['name' => 'Alex Morgan']);
        $case = $this->caseFor($manager);

        $proposal = \Simplydigital\AIManagement\Models\AIActionProposal::query()->create([
            'case_id' => $case->id,
            'action_type' => 'assign_case_engineer',
            'payload' => ['engineer_user_id' => $engineer->id],
            'source_hash' => str_repeat('a', 64),
            'payload_hash' => str_repeat('b', 64),
            'reason' => 'Human-reviewed candidate.',
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        app(AIProgressLogger::class)->proposal($proposal, 'prepared', $manager->id);
        app(AIProgressLogger::class)->proposal($proposal, 'approved', $manager->id);
        app(AIProgressLogger::class)->proposal($proposal, 'completed', $manager->id);

        $logs = TaskLogs::query()->where('ticket_id', $case->id)
            ->where('source', 'ai_agent')->get();
        $this->assertCount(3, $logs);
        foreach ($logs as $log) {
            $this->assertStringContainsString('Proposed engineer: Alex Morgan', $log->description);
            $this->assertStringNotContainsString('Proposed engineer user ID:', $log->description);
        }
    }

    private function assertLog(tickets $case, string $proposalId, string $event): void
    {
        $this->assertDatabaseHas('task_logs', [
            'ticket_id' => $case->id,
            'short_description' => 'AI:proposal:'.$proposalId.':'.$event,
            'source' => 'ai_agent',
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function caseFor(User $creator, ?User $assignee = null): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $creator->id,
            'assigned_to' => $assignee?->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Portal returns an HTTP 500',
            'description' => 'Test case for AI progress logging.',
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
