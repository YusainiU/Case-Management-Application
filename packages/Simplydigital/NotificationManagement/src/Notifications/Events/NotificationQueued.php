<?php

namespace Simplydigital\NotificationManagement\Notifications\Events;

use Simplydigital\NotificationManagement\Models\Notification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationQueued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification
    ) {}
}