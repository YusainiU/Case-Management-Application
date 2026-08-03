<?php

namespace Simplydigital\NotificationManagement\Notifications\Mail;

use Simplydigital\NotificationManagement\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification
    ) {}

    public function build(): static
    {
        return $this

            ->subject($this->notification->subject)

            ->view($this->notification->template->value)

            ->with($this->notification->payload);
    }
}