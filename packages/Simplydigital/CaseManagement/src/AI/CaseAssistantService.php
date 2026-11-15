<?php

namespace Simplydigital\CaseManagement\AI;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIProgressLogger;
use Simplydigital\AIManagement\Enums\AIStatuses;
use Simplydigital\CaseManagement\Models\tickets as CaseModel;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;

class CaseAssistantService
{
    public function requestAnalysis(
        CaseModel $case
    ): AIAnalysis {
        Gate::authorize(
            'case-assistant.analyze',
            $case
        );

        $context = app(
            CaseContextBuilder::class
        )->build($case);

        return DB::transaction(function () use ($case, $context): AIAnalysis {
            $analysis = AIAnalysis::create([
                'case_id' => $case->getKey(),
                'user_id' => auth()->id(),
                'type' => 'case_assistant',
                'model' => config('ai-management.model'),
                'prompt_version' => config('ai-management.case_assistant.prompt_version'),
                'status' => AIStatuses::Pending->value,
            ]);

            app(AIProgressLogger::class)->analysis($analysis, 'requested', auth()->id());

            GenerateCaseAssistantAnalysis::dispatch(
                analysisId: $analysis->id,
                context: $context,
            )->afterCommit();

            return $analysis;
        });
    }
}
