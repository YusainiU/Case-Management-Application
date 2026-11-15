<?php

namespace Simplydigital\AIManagement\Services;

use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\CaseManagement\Models\tickets;

class EngineerAssignmentProposalService
{
    public function propose(tickets $case, int $engineerUserId): AIActionProposal
    {
        return DB::transaction(function () use ($case, $engineerUserId): AIActionProposal {
            $locked = tickets::query()->lockForUpdate()->findOrFail($case->getKey());

            if ($locked->deleted_at !== null || $locked->closed_datetime !== null) {
                throw new \DomainException('Only open cases can be assigned.');
            }

            if ($locked->assigned_to === $engineerUserId) {
                throw new \DomainException('This engineer is already assigned to the case.');
            }

            $recommendations = app(EngineerRecommendationService::class)->recommend($locked);
            $choice = collect($recommendations)->firstWhere('user_id', $engineerUserId);
            if (! $choice || ! app(EngineerRecommendationService::class)->eligibleUser($engineerUserId)) {
                throw new \DomainException('Selected engineer is no longer eligible.');
            }

            $existing = AIActionProposal::query()
                ->where('case_id', $locked->id)
                ->where('action_type', 'assign_case_engineer')
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();
            if ($existing) {
                throw new \DomainException('There is already a pending engineer assignment proposal for this case.');
            }

            $payload = ['engineer_user_id' => $engineerUserId];
            $reason = count($choice['matched_skills'])
                ? 'Matched case text to listed skills: '.implode(', ', $choice['matched_skills']).'. '
                : 'No listed skill could be matched to the case text. Manual suitability verification is required. ';
            foreach ($choice['semantic_matches'] as $match) {
                $reason .= 'AI-suggested skill: '.$match['skill']
                    .' ('.$match['confidence'].'). Case evidence: "'
                    .$match['case_evidence'].'". Rationale: '.$match['reason'].'. ';
            }
            if ($choice['matched_certifications']) {
                $reason .= 'Matched active, unexpired certifications: '
                    .implode(', ', $choice['matched_certifications']).'. ';
            }
            $reason .= 'Current open-case count: '.$choice['open_cases']
                .'. This is a workload indicator, not confirmed availability.';

            $proposal = AIActionProposal::query()->create([
                'case_id' => $locked->id,
                'ai_analysis_id' => null,
                'action_type' => 'assign_case_engineer',
                'payload' => $payload,
                'source_hash' => EngineerAssignmentSnapshot::hash($locked),
                'payload_hash' => CaseCategorySnapshot::payloadHash($payload),
                'reason' => $reason,
                'status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);
            app(AIProgressLogger::class)->proposal($proposal, 'prepared', auth()->id());

            return $proposal;
        });
    }
}
