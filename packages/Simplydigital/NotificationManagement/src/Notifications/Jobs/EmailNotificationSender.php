<?php

namespace Simplydigital\NotificationManagement\Notifications\Jobs;

use Illuminate\Support\Facades\Mail;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationResult;
use Simplydigital\NotificationManagement\Notifications\Mail\NotificationMail;

class EmailNotificationSender
{
    public function send(
        Notification $notification
    ): NotificationResult {

        try {

            $to = $notification->toRecipients();
            $cc = $notification->ccRecipients();
            $bcc = $notification->bccRecipients();
            
            Mail::to(
                $to['email']
            )->cc(
                $cc
            )->bcc(
                $bcc
            )->send(

                $mail = new NotificationMail(
                    $notification
                )
            );
            
            return new NotificationResult(

                success: true,

                transport: 'smtp',

                messageId: null

            );

        } catch (\Throwable $e) {

            dd($e->getMessage(), $e);

        }

    }
}
