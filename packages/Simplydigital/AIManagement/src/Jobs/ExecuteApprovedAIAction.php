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
use Simplydigital\AIManagement\Services\EngineerRecommendationService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;

class ExecuteApprovedAIAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $proposalId) {}

    public function handle(CaseRepository $cases): void
    {
        if (! config('ai-management.approvals.execution_enabled', false)) {
            return;
        }

        DB::transaction(function () use ($cases) {
            $proposal = AIActionProposal::query()->lockForUpdate()->findOrFail($this->proposalId);

            if ($proposal->action_type === 'assign_case_engineer'
                && ! config('ai-management.engineer_assignment.execution_enabled', false)) {
                return;
            }

            if ($proposal->status === 'completed' || $proposal->status === 'stale') {
                return;
            }

            if ($proposal->status !== 'approved' || ! in_array($proposal->action_type, ['update_case_category', 'assign_case_engineer'], true)) {
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

            $sourceHash = $proposal->action_type === 'update_case_category'
                ? CaseCategorySnapshot::hash($case)
                : EngineerAssignmentSnapshot::hash($case);

            if ($case->closed_datetime !== null || $case->deleted_at !== null || $sourceHash !== $proposal->source_hash) {
                $proposal->update(['status' => 'stale']);
                return;
            }

            $reviewer = User::find($approval->reviewer_id);
            $gate = $proposal->action_type === 'update_case_category'
                ? 'ai-category.approve'
                : 'ai-engineer.approve';
            if (! $reviewer || ! Gate::forUser($reviewer)->allows($gate, $case)) {
                throw new \DomainException('Reviewer no longer has permission.');
            }

            if ($proposal->action_type === 'update_case_category') {
                $category = CaseCategory::tryFrom($proposal->payload['category'] ?? '');
                if (! $category) {
                    throw new \DomainException('Invalid category.');
                }

                $cases->updateTicket($case, ['category' => $category->value]);
            } else {
                $userId = filter_var(
                    $proposal->payload['engineer_user_id'] ?? null,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if (! $userId || ! app(EngineerRecommendationService::class)->eligibleUser($userId)) {
                    $proposal->update(['status' => 'stale']);
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
