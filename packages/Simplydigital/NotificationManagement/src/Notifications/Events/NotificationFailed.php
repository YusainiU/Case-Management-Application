<?php

namespace Simplydigital\NotificationManagement\Notifications\Events;

use Simplydigital\NotificationManagement\Models\Notification;
use Throwable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification,
        public readonly Throwable $exception,
    ) {
    }
}