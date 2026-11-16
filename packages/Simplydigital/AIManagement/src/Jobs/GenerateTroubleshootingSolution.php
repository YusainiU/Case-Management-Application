<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIProgressLogger;
use Simplydigital\AIManagement\Services\TroubleshootingSolutionService;
use Simplydigital\CaseManagement\Models\tickets;

class GenerateTroubleshootingSolution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $analysisId) {}

    public function handle(TroubleshootingSolutionService $service): void
    {
        $request = AIAnalysis::query()->findOrFail($this->analysisId);
        if (! in_array($request->status, ['pending', 'processing'], true)
            || $request->type !== 'troubleshooting_solution') {
            return;
        }

        if ($issue = $service->configurationIssue()) {
            $this->markFailed($request, $issue);
            return;
        }

        $case = tickets::query()->find($request->case_id);
        if (! $case || $case->closed_datetime !== null || $case->resolved_datetime !== null) {
            $this->markFailed($request, 'Case is no longer open for recommendations.');
            return;
        }

        $initial = AIAnalysis::query()->where('case_id', $case->id)
            ->where('type', 'case_assistant')->latest('id')->first();
        if ($initial?->status !== 'completed') {
            $this->markFailed($request, 'A completed Case Assistant analysis is required.');
            return;
        }

        $snapshot = hash('sha256', json_encode([
            $case->getRawOriginal('short_description'),
            $case->getRawOriginal('description'),
            $case->getRawOriginal('state'),
            $initial->id,
        ], JSON_THROW_ON_ERROR));

        $request->update(['status' => 'processing', 'error' => null]);
        try {
            $result = $service->generate($case, $initial);
            $case->refresh();
            $latest = AIAnalysis::query()->where('case_id', $case->id)
                ->where('type', 'case_assistant')->latest('id')->first();

            $current = hash('sha256', json_encode([
                $case->getRawOriginal('short_description'),
                $case->getRawOriginal('description'),
                $case->getRawOriginal('state'),
                $latest?->id,
            ], JSON_THROW_ON_ERROR));
            if (! hash_equals($snapshot, $current)
                || $case->closed_datetime !== null || $case->resolved_datetime !== null) {
                $this->markFailed($request, 'Case changed during solution research. Review and request a fresh recommendation if needed.');
                return;
            }

            DB::transaction(function () use ($request, $result): void {
                $request->update([
                    'status' => 'completed',
                    'result' => $result,
                    'model' => config('ai-management.model'),
                    'error' => null,
                ]);
                app(AIProgressLogger::class)->analysis($request, 'completed');
            });
        } catch (\Throwable $exception) {
            report($exception);
            $request->update([
                'status' => 'processing',
                'error' => 'Solution research interrupted; queue retry pending.',
            ]);
            throw $exception;
        }
    }

    private function markFailed(AIAnalysis $request, string $message): void
    {
        DB::transaction(function () use ($request, $message): void {
            $request->update(['status' => 'failed', 'error' => $message]);
            app(AIProgressLogger::class)->analysis($request, 'failed');
        });
    }

    public function failed(?\Throwable $exception): void
    {
        $request = AIAnalysis::query()->find($this->analysisId);
        if ($request && $request->status !== 'completed') {
            $this->markFailed($request, 'Solution recommendation failed after retries. Review application logs.');
        }
        if ($exception) {
            report($exception);
        }
    }
}
