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
        
        $templateView = $this->notification->getTemplateView();

        return $this

            ->subject($this->notification->subject)

            ->view($templateView)

            ->with($this->notification->payload);
    }
}