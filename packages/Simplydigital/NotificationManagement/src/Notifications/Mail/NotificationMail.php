<?php

namespace Simplydigital\NotificationManagement\Notifications\Mail;

use Simplydigital\NotificationManagement\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;

class NotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification
    ) {}

    public function content(): Content
    {
        $templateView = $this->notification->getTemplateView();
        return new Content(
            view: $templateView,
            with: [
                'notification' => $this->notification,
                ...get_object_vars($this->notification->view_data),
            ],
        );
    }

    // Old Laravel Mailable build() method, replaced with content() method in Laravel 10
    //-----------------------------------------------------------------------------------
    // public function build(): static
    // {       
    //     $templateView = $this->notification->getTemplateView();
    //     return $this
    //         ->subject($this->notification->subject)
    //         ->view($templateView)
    //         ->with($this->notification->payload);
    // }    

}