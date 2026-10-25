<?php

namespace Simplydigital\NotificationManagement\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\Notifications\Services\QueuedJobRunner;
use Throwable;

#[Layout('components.layouts.app')]
class QueuedMailJobs extends Component
{
    use WithPagination;

    public function mount()
    {
        $this->authorize('viewAny', Notification::class);
    }

    public function runNow(int $jobId): void
    {
        $this->authorize('update', Notification::class);
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
