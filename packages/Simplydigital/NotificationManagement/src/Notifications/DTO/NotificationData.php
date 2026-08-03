<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\NotificationManagement\Enums\{
    NotificationPriority,
    NotificationStatus,
    NotificationTemplate,
    NotificationChannel,
};

class NotificationData
{
    public function __construct(

        public string $subject,

        public string $view,

        public array $viewData = [],

        public array $to = [],

        public array $cc = [],

        public array $bcc = [],
        
        public ?NotificationTemplate $template = NotificationTemplate::EmailNotification,

        public ?string $payload = null,

        public ?string $scheduledAt = null,

        public Model $trigger,

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


    ) {}
}