<?php

namespace Simplydigital\AIManagement\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Models\tickets;

class CategoryProposalService
{
    public function fromAnalysis(AIAnalysis $analysis): ?AIActionProposal
    {
        if ($analysis->type !== 'case_assistant' || $analysis->status !== 'completed') {
            throw new \DomainException('Only completed case assistant analyses are supported.');
        }

        $category = CaseCategory::tryFrom($analysis->result['suggested_category'] ?? '');
        if (!$category) {
            return null;
        }

        return DB::transaction(function () use ($analysis, $category) {
            $case = tickets::query()->lockForUpdate()->findOrFail($analysis->case_id);

            if ($case->closed_datetime !== null || $case->deleted_at !== null) {
                throw new \DomainException('Case is closed or deleted.');
            }

            if ($case->category === $category) {
                return null;
            }

            $existing = AIActionProposal::query()
                ->where('ai_analysis_id', $analysis->id)
                ->where('action_type', 'update_case_category')
                ->first();

            if ($existing) {
                return $existing;
            }

            $payload = ['category' => $category->value];

            return AIActionProposal::create([
                'id' => (string) Str::uuid(),
                'case_id' => $case->id,
                'ai_analysis_id' => $analysis->id,
                'action_type' => 'update_case_category',
                'payload' => $payload,
                'source_hash' => CaseCategorySnapshot::hash($case),
                'payload_hash' => CaseCategorySnapshot::payloadHash($payload),
                'reason' => (string) ($analysis->result['summary'] ?? 'AI category recommendation'),
                'status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);
        });
    }
}
