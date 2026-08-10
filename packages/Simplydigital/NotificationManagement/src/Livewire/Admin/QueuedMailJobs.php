<?php

namespace Simplydigital\NotificationManagement\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\NotificationManagement\Notifications\Services\QueuedJobRunner;
use Throwable;

class QueuedMailJobs extends Component
{
    use WithPagination;

public function runNow(int $jobId): void
{
    try {
        app(QueuedJobRunner::class)->run($jobId);

        session()->flash(
            'success',
            "Job #{$jobId} executed successfully."
        );
    } catch (Throwable $e) {
        report($e);

        session()->flash(
            'error',
            "Job #{$jobId} failed: {$e->getMessage()}"
        );
    }
}

    public function render()
    {
        $queueName = 'notifications';
        $jobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->orderBy('id')
            ->paginate(20);

        return view(
            'notification-management::livewire.admin.queued-mail-jobs',
            compact('jobs')
        );
    }
}