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
use Simplydigital\AIManagement\Services\AIProgressLogger;
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

            \Illuminate\Support\Facades\DB::transaction(function () use ($analysis, $result): void {
                $analysis->update([
                    'status' => AIStatuses::Completed->value,
                    'result' => $result->toArray(),
                    'model' => config('ai-management.model'),
                ]);
                app(AIProgressLogger::class)->analysis($analysis, 'completed');
            });
        } catch (\Throwable $exception) {
            report($exception);
            // Keep the analysis retryable. Log terminal failure only when
            // Laravel exhausts its attempts.
            $analysis->update([
                'status' => AIStatuses::Processing->value,
                'error' => 'Analysis interrupted; retry pending.',
            ]);

            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $analysis = AIAnalysis::query()->find($this->analysisId);
        if ($analysis && $analysis->status !== AIStatuses::Completed->value) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($analysis): void {
                $analysis->update([
                    'status' => AIStatuses::Failed->value,
                    'error' => 'AI analysis failed after retries. Review application logs.',
                ]);
                app(AIProgressLogger::class)->analysis($analysis, 'failed');
            });
        }
        if ($exception) {
            report($exception);
        }
    }
}