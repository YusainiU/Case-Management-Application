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
        DB::transaction(function () use ($cases) {
            $proposal = AIActionProposal::query()->lockForUpdate()->findOrFail($this->proposalId);

            if ($proposal->status === 'completed' || $proposal->status === 'stale') {
                return;
            }

            if ($proposal->status !== 'approved' || $proposal->action_type !== 'update_case_category') {
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

            if ($case->closed_datetime !== null || CaseCategorySnapshot::hash($case) !== $proposal->source_hash) {
                $proposal->update(['status' => 'stale']);
                return;
            }

            $reviewer = User::find($approval->reviewer_id);
            if (!$reviewer || !Gate::forUser($reviewer)->allows('ai-category.approve', $case)) {
                throw new \DomainException('Reviewer no longer has permission.');
            }

            $category = CaseCategory::tryFrom($proposal->payload['category'] ?? '');
            if (!$category) {
                throw new \DomainException('Invalid category.');
            }

            // Existing repository path preserves ticket model events and activity logging.
            $cases->updateTicket($case, ['category' => $category->value]);

            DB::table('ai_action_executions')->updateOrInsert(
                ['proposal_id' => $proposal->id],
                ['status' => 'completed', 'error' => null, 'created_at' => now(), 'updated_at' => now()]
            );

            $proposal->update(['status' => 'completed']);
        });
    }
}
