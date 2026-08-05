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

final readonly class NotificationData
{
    public function __construct(

        public string $subject,

        public ?NotificationViewData $viewData,

        public Model $trigger,

        public array $to = [],

        public array $cc = [],

        public array $bcc = [],        

        public ?NotificationTemplate $template = NotificationTemplate::EmailNotification,

        public ?string $scheduledAt = null,

        public ?NotificationChannel $channel = NotificationChannel::Email,

        public ?User $createdBy = null,

        public NotificationPriority $priority = NotificationPriority::Normal,

        public array $attachments = []

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


    ) {}
}