<?php

namespace Simplydigital\AIManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Simplydigital\AIManagement\Jobs\ExecuteApprovedAIAction;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\CaseManagement\Models\tickets;

class AIApprovalService
{
    public function approve(string $proposalId, User $reviewer): void
    {
        DB::transaction(function () use ($proposalId, $reviewer) {
            $proposal = AIActionProposal::query()->lockForUpdate()->findOrFail($proposalId);
            $case = tickets::query()->lockForUpdate()->findOrFail($proposal->case_id);

            Gate::forUser($reviewer)->authorize(
                match ($proposal->action_type) {
                    'update_case_category' => 'ai-category.approve',
                    'assign_case_engineer' => 'ai-engineer.approve',
                    default => throw new \DomainException('Unsupported action type.'),
                },
                $case,
            );

            if ($proposal->status !== 'pending' || $proposal->expires_at->isPast()) {
                throw new \DomainException('Proposal is no longer pending.');
            }

            $sourceHash = match ($proposal->action_type) {
                'update_case_category' => CaseCategorySnapshot::hash($case),
                'assign_case_engineer' => EngineerAssignmentSnapshot::hash($case),
                default => throw new \DomainException('Unsupported action type.'),
            };

            if ($sourceHash !== $proposal->source_hash
                || CaseCategorySnapshot::payloadHash($proposal->payload) !== $proposal->payload_hash) {
                throw new \DomainException('Proposal is invalid or stale.');
            }

            if ($proposal->action_type === 'assign_case_engineer'
                && ! app(EngineerRecommendationService::class)->eligibleUser(
                    (int) ($proposal->payload['engineer_user_id'] ?? 0)
                )) {
                throw new \DomainException('Engineer is no longer eligible.');
            }

            $proposal->update(['status' => 'approved']);

            DB::table('ai_action_approvals')->insert([
                'proposal_id' => $proposal->id,
                'reviewer_id' => $reviewer->id,
                'decision' => 'approved',
                'payload_hash' => $proposal->payload_hash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $executionEnabled = match ($proposal->action_type) {
                'update_case_category' => config('ai-management.approvals.execution_enabled', false),
                'assign_case_engineer' => config('ai-management.engineer_assignment.execution_enabled', false),
                default => false,
            };

            if ($executionEnabled) {
                ExecuteApprovedAIAction::dispatch($proposal->id)->afterCommit();
            }
        });
    }

    public function reject(string $proposalId, User $reviewer, ?string $reason = null): void
    {
        DB::transaction(function () use ($proposalId, $reviewer, $reason) {
            $proposal = AIActionProposal::query()->lockForUpdate()->findOrFail($proposalId);
            $case = tickets::findOrFail($proposal->case_id);
            Gate::forUser($reviewer)->authorize(
                match ($proposal->action_type) {
                    'update_case_category' => 'ai-category.approve',
                    'assign_case_engineer' => 'ai-engineer.approve',
                    default => throw new \DomainException('Unsupported action type.'),
                },
                $case,
            );

            if ($proposal->status !== 'pending') {
                throw new \DomainException('Proposal is no longer pending.');
            }

            $proposal->update(['status' => 'rejected']);

            DB::table('ai_action_approvals')->insert([
                'proposal_id' => $proposal->id,
                'reviewer_id' => $reviewer->id,
                'decision' => 'rejected',
                'reason' => $reason,
                'payload_hash' => $proposal->payload_hash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
