<?php

namespace Simplydigital\AIManagement\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIProgressLogger;

/**
 * Reconstruct the AI lifecycle events supported by the existing audit records.
 * Does not invent intermediate statuses for legacy proposals.
 */
class BackfillAIProgressLogs extends Command
{
    protected $signature = 'ai:backfill-case-progress {--write : Write missing entries (default: preview only)}';

    protected $description = 'Backfill internal case progress logs from historic AI proposal and analysis records';

    public function handle(AIProgressLogger $logger): int
    {
        $write = (bool) $this->option('write');
        $proposals = 0;
        $analyses = 0;

        AIActionProposal::query()->whereHas('case')->with(['approvals', 'execution'])
            ->orderBy('id')->chunk(100, function ($items) use (&$proposals, $logger, $write): void {
                foreach ($items as $proposal) {
                    $proposals++;
                    if (! $write) {
                        continue;
                    }
                    DB::transaction(function () use ($proposal, $logger): void {
                        $logger->proposal($proposal, 'prepared', null, $proposal->created_at);
                        foreach ($proposal->approvals as $decision) {
                            if (in_array($decision->decision, ['approved', 'rejected'], true)) {
                                $logger->proposal(
                                    $proposal,
                                    $decision->decision,
                                    (int) $decision->reviewer_id,
                                    $decision->created_at,
                                );
                            }
                        }
                        if ($proposal->execution) {
                            $logger->proposal($proposal, 'queued', null, $proposal->execution->created_at);
                            if (in_array($proposal->execution->status, ['completed', 'stale', 'failed'], true)) {
                                $logger->proposal($proposal, $proposal->execution->status, null, $proposal->execution->updated_at);
                            }
                        }
                    });
                }
            });

        AIAnalysis::query()->whereHas('case')->orderBy('id')
            ->chunk(100, function ($items) use (&$analyses, $logger, $write): void {
                foreach ($items as $analysis) {
                    $analyses++;
                    if (! $write) {
                        continue;
                    }
                    DB::transaction(function () use ($analysis, $logger): void {
                        $logger->analysis($analysis, 'requested', null, $analysis->created_at);
                        if (in_array($analysis->status, ['completed', 'failed'], true)) {
                            $logger->analysis($analysis, $analysis->status, null, $analysis->updated_at);
                        }
                    });
                }
            });

        $verb = $write ? 'Processed' : 'Previewed';
        $this->info("{$verb} {$proposals} AI proposals and {$analyses} AI analyses.");
        if (! $write) {
            $this->comment('No data changed. Pass --write to create missing internal case progress entries.');
        } else {
            $this->comment('Backfill complete. Running --write again does not recreate existing event keys.');
        }

        return self::SUCCESS;
    }
}
