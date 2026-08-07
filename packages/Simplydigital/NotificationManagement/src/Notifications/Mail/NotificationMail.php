<?php

namespace Simplydigital\NotificationManagement\Notifications\Mail;

use Simplydigital\NotificationManagement\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->subject,
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function content(): Content
    {
        $templateView = $this->notification->getTemplateView();
        $viewData = $this->notification->view_data;
        $mail = new Content(
            view: $templateView,
            with: [
                'notification' => $this->notification,
                ...(\is_array($viewData) ? $viewData : $viewData->toArray()),
            ],
        );
        return $mail;
    }

  

}