<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Enums\{
    NotificationPriority,
    NotificationTemplate,
    NotificationChannel,
};
use InvalidArgumentException;

/**
 * @param EmailRecipient[] $to
 * @param EmailRecipient[] $cc
 * @param EmailRecipient[] $bcc
 * @param NotificationAttachmentData[] $attachments
 */

final readonly class NotificationData
{
    public function __construct(

        public string $subject,

        public ?NotificationViewData $viewData,

        public Model $trigger,

        public array $to = [],

        public array $cc = [],

        public array $bcc = [],        

        public ?string $scheduledAt = null,

        public ?User $createdBy = null,

        public array $attachments = [],       

        public NotificationTemplate $template = NotificationTemplate::EmailNotification,

        public NotificationChannel $channel = NotificationChannel::Email,

        public NotificationPriority $priority = NotificationPriority::Normal,

        /*
         Example of $attachments = [
            new NotificationAttachmentData(
                disk: 'public',
                path: 'quotes/quote-5521.pdf'
            ),

            new NotificationAttachmentData(
                disk: 'public',
                path: 'photos/job123.jpg'
            ),         
         ]
        */

         /*
            To use ... example
                $data = new NotificationData(
                channel: NotificationChannel::Email,
                priority: NotificationPriority::Normal,
                template: NotificationTemplate::Generic,
                subject: 'Test Notification',
            );
         */


    ) {

        $this->validateRecipients($this->to);
        $this->validateRecipients($this->cc);
        $this->validateRecipients($this->bcc);
        $this->validateAttachments($this->attachments);

    }

    private function validateRecipients(array $recipients): void
    {
        foreach ($recipients as $recipient) {
            if (! $recipient instanceof EmailRecipient) {
                throw new InvalidArgumentException(
                    'All recipients must be EmailRecipientData instances.'
                );
            }
        }
    }

    private function validateAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof NotificationAttachmentData) {
                throw new InvalidArgumentException(
                    'All attachments must be NotificationAttachmentData instances.'
                );
            }
        }
    }    
}