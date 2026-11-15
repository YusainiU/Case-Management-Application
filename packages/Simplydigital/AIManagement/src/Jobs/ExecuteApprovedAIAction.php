<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Services\CaseCategorySnapshot;
use Simplydigital\AIManagement\Services\EngineerAssignmentSnapshot;
use Simplydigital\AIManagement\Services\CaseWorkStartSnapshot;
use Simplydigital\AIManagement\Services\EngineerRecommendationService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;

class ExecuteApprovedAIAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $proposalId) {}

    // Transient failures are retried automatically; exhausted jobs require
    // an operator to investigate, not a second approval or silent replay.
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function failed(?\Throwable $exception): void
    {
        // Laravel calls failed() after retries are exhausted.
        // Keep the full exception in secure logs, not in the reviewer UI.
        if ($exception) {
            report($exception);
        }

        DB::transaction(function (): void {
            $proposal = AIActionProposal::query()
                ->lockForUpdate()
                ->find($this->proposalId);

            if (! $proposal || $proposal->status !== 'approved') {
                return;
            }

            $proposal->update(['status' => 'failed']);

            DB::table('ai_action_executions')->updateOrInsert(
                ['proposal_id' => $proposal->id],
                [
                    'status' => 'failed',
                    'error' => 'Execution failed after queue retries. Review application logs and failed jobs before taking action.',
                    'updated_at' => now(),
                ]
            );
        });
    }

    public function handle(CaseRepository $cases): void
    {
        DB::transaction(function () use ($cases) {
            $proposal = AIActionProposal::query()->lockForUpdate()->findOrFail($this->proposalId);

            $executionEnabled = match ($proposal->action_type) {
                'update_case_category' => config('ai-management.approvals.execution_enabled', false),
                'assign_case_engineer' => config('ai-management.engineer_assignment.execution_enabled', false),
                'start_case_work' => config('ai-management.work_start.execution_enabled', false),
                default => false,
            };
            if (! $executionEnabled) {
                return;
            }

            // Terminal outcomes are not replayable, including exhausted failures.
            if (in_array($proposal->status, ['completed', 'stale', 'failed'], true)) {
                return;
            }

            if ($proposal->status !== 'approved' || ! in_array($proposal->action_type, ['update_case_category', 'assign_case_engineer', 'start_case_work'], true)) {
                throw new \DomainException('This action is not approved or supported.');
            }

            $approval = DB::table('ai_action_approvals')
                ->where('proposal_id', $proposal->id)
                ->where('decision', 'approved')
                ->where('payload_hash', $proposal->payload_hash)
                ->first();

            if (!$approval || $proposal->expires_at->isPast()
                || CaseCategorySnapshot::payloadHash($proposal->payload) !== $proposal->payload_hash) {
                throw new \DomainException('Approval is missing or invalid.');
            }

            $case = tickets::query()->lockForUpdate()->findOrFail($proposal->case_id);

            $sourceHash = match ($proposal->action_type) {
                'update_case_category' => CaseCategorySnapshot::hash($case),
                'assign_case_engineer' => EngineerAssignmentSnapshot::hash($case),
                'start_case_work' => CaseWorkStartSnapshot::hash($case),
            };

            if ($case->closed_datetime !== null || $case->deleted_at !== null || $sourceHash !== $proposal->source_hash) {
                $proposal->update(['status' => 'stale']);
                DB::table('ai_action_executions')->updateOrInsert(
                    ['proposal_id' => $proposal->id],
                    ['status' => 'stale', 'error' => 'Case or engineer changed before execution.', 'updated_at' => now()]
                );
                return;
            }

            $reviewer = User::find($approval->reviewer_id);
            $gate = $proposal->action_type === 'update_case_category'
                ? 'ai-category.approve'
                : ($proposal->action_type === 'start_case_work' ? 'ai-work-start.approve' : 'ai-engineer.approve');
            if (! $reviewer || ! Gate::forUser($reviewer)->allows($gate, $case)) {
                throw new \DomainException('Reviewer no longer has permission.');
            }

            if ($proposal->action_type === 'update_case_category') {
                $category = CaseCategory::tryFrom($proposal->payload['category'] ?? '');
                if (! $category) {
                    throw new \DomainException('Invalid category.');
                }

                $cases->updateTicket($case, ['category' => $category->value]);
            } elseif ($proposal->action_type === 'start_case_work') {
                if (($proposal->payload['state'] ?? null) !== CaseState::In_Progress->value
                    || $case->state !== CaseState::Open || ! $case->assigned_to
                    || $case->resolved_datetime !== null) {
                    $proposal->update(['status' => 'stale']);
                    DB::table('ai_action_executions')->updateOrInsert(
                        ['proposal_id' => $proposal->id],
                        ['status' => 'stale', 'error' => 'Case is not eligible for Start Work.', 'updated_at' => now()]
                    );
                    return;
                }

                $cases->updateCaseState($case, CaseState::In_Progress);
            } else {
                $userId = filter_var(
                    $proposal->payload['engineer_user_id'] ?? null,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if (! $userId || ! app(EngineerRecommendationService::class)->eligibleUser($userId)) {
                    $proposal->update(['status' => 'stale']);
                    DB::table('ai_action_executions')->updateOrInsert(
                        ['proposal_id' => $proposal->id],
                        ['status' => 'stale', 'error' => 'Selected engineer is no longer eligible.', 'updated_at' => now()]
                    );
                    return;
                }

                // Repository update retains ticket events and activity history.
                $cases->updateTicket($case, ['assigned_to' => $userId]);
            }

            DB::table('ai_action_executions')->updateOrInsert(
                ['proposal_id' => $proposal->id],
                ['status' => 'completed', 'error' => null, 'created_at' => now(), 'updated_at' => now()]
            );

            $proposal->update(['status' => 'completed']);
        });
    }
}
