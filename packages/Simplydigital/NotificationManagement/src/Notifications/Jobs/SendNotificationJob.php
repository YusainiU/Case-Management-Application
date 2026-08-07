<?php

namespace Simplydigital\NotificationManagement\Notifications\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Simplydigital\NotificationManagement\Models\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 60;

    public function __construct(
        public Notification $notification
    ) {}

    public function handle(
        EmailNotificationSender $sender
    ): void {

        $this->notification->refresh();

        $this->notification->markSending();

        try {

            $result = $sender->send(
                $this->notification
            );

            $this->notification->markSent(
                $result
            );

        } catch (Throwable $exception) {

            $this->notification->markFailed(
                $exception
            );

            throw $exception;
        }
    }

}
