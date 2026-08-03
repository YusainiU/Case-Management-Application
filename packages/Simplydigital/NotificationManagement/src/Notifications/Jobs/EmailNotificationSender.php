<?php

namespace Simplydigital\NotificationManagement\Jobs;

use Illuminate\Support\Facades\Mail;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\DTO\NotificationResult;
use Simplydigital\NotificationManagement\Notifications\Mail\NotificationMail;
class EmailNotificationSender
{
    public function send(
        Notification $notification
    ): NotificationResult
    {
        Mail::to(
            $notification->toRecipients()
        )->cc(
            $notification->ccRecipients()
        )->bcc(
            $notification->bccRecipients()
        )->send(

            new NotificationMail(
                $notification
            )

        );

        return new NotificationResult(

            success:true,

            transport:'smtp',

            messageId:null

        );
    }    
}