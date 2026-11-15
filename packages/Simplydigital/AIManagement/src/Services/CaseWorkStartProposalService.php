<?php

namespace Simplydigital\AIManagement\Services;

use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;

class CaseWorkStartProposalService
{
    public function propose(tickets $case): AIActionProposal
    {
        return DB::transaction(function () use ($case): AIActionProposal {
            $locked = tickets::query()->lockForUpdate()->findOrFail($case->getKey());

            if ($locked->deleted_at !== null || $locked->closed_datetime !== null
                || $locked->resolved_datetime !== null
                || $locked->state !== CaseState::Open) {
                throw new \DomainException('Only an open, unresolved case can enter In Progress.');
            }

            if (! $locked->assigned_to) {
                throw new \DomainException('Assign an engineer before preparing a Start Work proposal.');
            }

            $existing = AIActionProposal::query()
                ->where('case_id', $locked->id)
                ->where('action_type', 'start_case_work')
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();

            if ($existing) {
                throw new \DomainException('This case already has a pending Start Work proposal.');
            }

            $payload = ['state' => CaseState::In_Progress->value];
            return AIActionProposal::query()->create([
                'case_id' => $locked->id,
                'ai_analysis_id' => null,
                'action_type' => 'start_case_work',
                'payload' => $payload,
                'source_hash' => CaseWorkStartSnapshot::hash($locked),
                'payload_hash' => CaseCategorySnapshot::payloadHash($payload),
                'reason' => 'The case is Open and has an assigned engineer. Start Work may be appropriate; '
                    .'the reviewer must confirm work has actually begun. '
                    .'No work activity or diagnostic result is inferred automatically.',
                'status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);
        });
    }
}
