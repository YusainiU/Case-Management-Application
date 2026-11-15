<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\SemanticEngineerSkillService;
use Simplydigital\AIManagement\Services\AIProgressLogger;
use Simplydigital\CaseManagement\Models\tickets;

class GenerateSemanticEngineerSkills implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public int $analysisId) {}

    public function handle(SemanticEngineerSkillService $service): void
    {
        $analysis = AIAnalysis::query()->findOrFail($this->analysisId);
        if (! in_array($analysis->status, ['pending', 'processing'], true)) {
            return;
        }

        if (! config('ai-management.engineer_semantic.enabled', false)) {
            $analysis->update(['status' => 'failed', 'error' => 'Semantic matching is disabled.']);
            app(AIProgressLogger::class)->analysis($analysis, 'failed');
            return;
        }

        if ($issue = $service->configurationIssue()) {
            $analysis->update([
                'status' => 'failed',
                'error' => $issue.' Exact skill matching remains available.',
            ]);
            app(AIProgressLogger::class)->analysis($analysis, 'failed');
            return;
        }

        $case = tickets::query()->find($analysis->case_id);
        if (! $case || $case->closed_datetime !== null) {
            $analysis->update(['status' => 'failed', 'error' => 'Case is no longer available for matching.']);
            app(AIProgressLogger::class)->analysis($analysis, 'failed');
            return;
        }

        $context = $service->context($case);
        $analysis->update(['status' => 'processing']);

        try {
            $result = $service->analyze($context);

            // Never attach old AI results to a changed case or skill catalogue.
            $case->refresh();
            if ($service->context($case)['hash'] !== $context['hash']) {
                $analysis->update(['status' => 'failed', 'error' => 'Case or skills changed. Request a fresh match.']);
                app(AIProgressLogger::class)->analysis($analysis, 'failed');
                return;
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($analysis, $result): void {
                $analysis->update([
                    'status' => 'completed',
                    'result' => $result,
                    'model' => config('ai-management.model'),
                    'error' => null,
                ]);
                app(AIProgressLogger::class)->analysis($analysis, 'completed');
            });
        } catch (\Throwable $exception) {
            report($exception);
            // Keep this job retryable; the failed() hook marks terminal failure
            // only after Laravel has exhausted attempts.
            $analysis->update([
                'status' => 'processing',
                'error' => 'Semantic matching encountered an error. The queue will retry.',
            ]);
            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $analysis = AIAnalysis::query()->find($this->analysisId);
        if ($analysis && $analysis->status !== 'completed') {
            $analysis->update([
                'status' => 'failed',
                'error' => 'Semantic matching failed after retries. Exact skill matching remains available.',
            ]);
            app(AIProgressLogger::class)->analysis($analysis, 'failed');
        }
        if ($exception) {
            report($exception);
        }
    }
}
