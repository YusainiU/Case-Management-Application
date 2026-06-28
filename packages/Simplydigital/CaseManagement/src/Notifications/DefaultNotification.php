<?php

namespace Simplydigital\CaseManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DefaultNotification extends Notification
{
    use Queueable;
    private array $messages;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line($this->messages["from"])
            ->line($this->messages["title"])
            ->line($this->messages["greeting"])
            ->line($this->messages["content"])
            ->line($this->messages["datetime"])
            //->action('Notification Action', url('/'))
            ->line('This notification is from STEPS');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
