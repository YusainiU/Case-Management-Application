<?php

namespace Simplydigital\NotificationManagement\Notifications\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class QueuedJobRunner
{
    public function run(int $jobId): void
    {
        $job = DB::table('jobs')
            ->where('id', $jobId)
            ->lockForUpdate()
            ->first();

        if (! $job) {
            throw new RuntimeException(
                "Queued job #{$jobId} no longer exists."
            );
        }

        $payload = json_decode($job->payload, true);

        if (! isset($payload['data']['command'])) {
            throw new RuntimeException(
                "Queued job #{$jobId} does not contain a Laravel command."
            );
        }

        $command = unserialize($payload['data']['command']);

        try {
            app()->call([$command, 'handle']);

            DB::table('jobs')
                ->where('id', $jobId)
                ->delete();
        } catch (Throwable $exception) {
            DB::table('jobs')
                ->where('id', $jobId)
                ->update([
                    'attempts' => $job->attempts + 1,
                ]);

            throw $exception;
        }
    }
}