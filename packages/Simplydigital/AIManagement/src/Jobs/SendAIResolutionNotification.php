<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\AIManagement\Services\AIProgressLogger;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\EmailService;

class SendAIResolutionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $caseId) {}

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(EmailService $emails): void
    {
        // Only one queued worker may claim this notification at a time.
        // The unique case_id in the outbox prevents repeat submissions.
        $claimed = DB::transaction(function (): bool {
            $record = DB::table('ai_resolution_notifications')
                ->where('case_id', $this->caseId)
                ->lockForUpdate()
                ->first();

            if (! $record || $record->status !== 'pending') {
                return false;
            }

            $case = tickets::query()->find($this->caseId);
            if (! $case || $case->state !== CaseState::Resolved || ! $case->resolved_datetime) {
                DB::table('ai_resolution_notifications')->where('case_id', $this->caseId)->update([
                    'status' => 'skipped',
                    'error' => 'Case is no longer resolved or is unavailable.',
                    'updated_at' => now(),
                ]);
                app(AIProgressLogger::class)->resolutionNotification($this->caseId, 'skipped');

                return false;
            }

            DB::table('ai_resolution_notifications')->where('case_id', $this->caseId)->update([
                'status' => 'sending',
                'attempts' => $record->attempts + 1,
                'error' => null,
                'updated_at' => now(),
            ]);

            return true;
        });

        if (! $claimed) {
            return;
        }

        try {
            $case = tickets::query()->findOrFail($this->caseId);
            // Exactly the same email implementation used by Close & Resolve.
            // A false result means the normal email service found no recipient.
            $result = $emails->caseResolved($case);

            DB::table('ai_resolution_notifications')->where('case_id', $this->caseId)->update([
                'status' => $result === false ? 'skipped' : 'sent',
                'sent_at' => $result === false ? null : now(),
                'error' => $result === false ? 'No customer recipient was available.' : null,
                'updated_at' => now(),
            ]);

            app(AIProgressLogger::class)->resolutionNotification(
                $this->caseId, $result === false ? 'skipped' : 'sent'
            );
        } catch (\Throwable $exception) {
            DB::table('ai_resolution_notifications')->where('case_id', $this->caseId)
                ->where('status', 'sending')->update([
                    'status' => 'pending',
                    'error' => 'Customer notification failed; awaiting retry.',
                    'updated_at' => now(),
                ]);

            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }

        DB::table('ai_resolution_notifications')->where('case_id', $this->caseId)
            ->whereIn('status', ['pending', 'sending'])->update([
                'status' => 'failed',
                'error' => 'Customer notification failed after queue retries. Review the failed job.',
                'updated_at' => now(),
            ]);
        app(AIProgressLogger::class)->resolutionNotification($this->caseId, 'failed');
    }
}
