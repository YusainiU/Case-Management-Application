<?php

namespace Simplydigital\CaseManagement\Services;

use Illuminate\Database\Eloquent\Model;
use Simplydigital\NotificationManagement\Notifications\DTO\EmailRecipient;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;

/**
 * @param EmailRecipient[] $to
 * @param EmailRecipient[] $cc
 * @param EmailRecipient[] $bcc
 */

class EmailService
{
    public function __construct(
        public array $to,        
        public NotificationViewData $viewData,
        public Model $trigger,
        public string $subject,
        public array $cc = [],
        public array $bcc = [],        
    ) {

        $this->sendEmail();

    }

    public function sendEmail()
    {
        $mail = app(NotificationService::class);

        $data = new NotificationData(
            subject: $this->subject,    
            to: $this->to,            
            viewData: $this->viewData,
            trigger: $this->trigger,
            cc: $this->cc,
            bcc: $this->bcc,
        );

        return $mail->send($data);        
    }
}
