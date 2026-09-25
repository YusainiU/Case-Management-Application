<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\AI\Services\CaseAssistant;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Enums\AIStatuses;

class GenerateCaseAssistantAnalysis implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int|string $analysisId,
        public CaseContext $context,
    ) {
        $this->onQueue(
            config('ai-management.case_assistant.queue','default')
        );
    }

    public function handle(
        CaseAssistant $assistant
    ): void {
        $analysis = AIAnalysis::findOrFail(
            $this->analysisId
        );

        $analysis->update([
            'status' => AIStatuses::Processing->value,
            'error' => null,
        ]);

        try {
            $result = $assistant->analyze(
                $this->context
            );

            $analysis->update([
                'status' => AIStatuses::Completed->value,
                'result' => $result->toArray(),
                'model' => config('ai-management.model'),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $analysis->update([
                'status' => AIStatuses::Failed->value,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}