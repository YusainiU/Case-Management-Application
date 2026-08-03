<?php

namespace Simplydigital\NotificationManagement\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Simplydigital\NotificationManagement\Enums\NotificationStatus;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\Models\NotificationRecipient;
use Simplydigital\NotificationManagement\Models\NotificationAttachment;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\Events\NotificationCreated;
use Simplydigital\NotificationManagement\Jobs\SendNotificationJob;
use Simplydigital\NotificationManagement\Exceptions\{
    MissingSubjectException,
    MissingTemplateException,
    NoRecipientsException,
    AttachmentNotFoundException,
};



class NotificationService
{
    public function send(NotificationData $data) {

        // Validate the incoming request
        $this->validate($data);

        return DB::transaction(function () use ($data) {

            /*
             |--------------------------------------------------------------------------
             | Create the Notification
             |--------------------------------------------------------------------------
             */

            $notification = $this->createNotification($data);

            /*
             |--------------------------------------------------------------------------
             | Store Recipients
             |--------------------------------------------------------------------------
             */

            $this->createRecipients($notification, $data);

            /*
             |--------------------------------------------------------------------------
             | Store Attachments
             |--------------------------------------------------------------------------
             */

            $this->storeAttachments($notification, $data);

            /*
             |--------------------------------------------------------------------------
             | Fire Created Event
             |--------------------------------------------------------------------------
             */

            event(new NotificationCreated($notification));

            /*
             |--------------------------------------------------------------------------
             | Mark as Queued
             |--------------------------------------------------------------------------
             */

            $notification->status = NotificationStatus::Queued;
            $notification->queued_at = now();
            $notification->save();

            /*
             |--------------------------------------------------------------------------
             | Queue the Job
             |--------------------------------------------------------------------------
             */

            SendNotificationJob::dispatch($notification)
                ->afterCommit();

            /*
             |--------------------------------------------------------------------------
             | Fire Queued Event
             |--------------------------------------------------------------------------
             */

            event(new NotificationQueued($notification));

            return $notification;
        });    


    }

    public function resend(Notification $notification) {}

    public function cancel(Notification $notification) {}

    protected function validate(NotificationData $data): void
    {
        if (empty($data->to)) {

            throw new NoRecipientsException;
        }

        if (blank($data->subject)) {

            throw new MissingSubjectException;
        }

        if (! $data->template) {

            throw new MissingTemplateException;
        }
    }

    protected function createNotification(NotificationData $data): Notification
    {
        $notification = new Notification;

        $notification->uuid = (string) Str::uuid();

        $notification->status = NotificationStatus::Pending->value;

        $notification->channel = $data->channel->value;

        $notification->priority = $data->priority->value;

        $notification->subject = $data->subject;

        $notification->template = $data->template->value;

        $notification->payload = $data->payload;

        $notification->created_by = $data->createdBy ?? Auth::id();

        $notification->scheduled_at = $data->scheduledAt;

        // Associate the model that triggered the notification
        if ($data->trigger) {
            $notification->trigger_id = $data->trigger->getKey();
            $notification->trigger_type = \get_class($data->trigger);
        }

        $notification->save();

        return $notification;
    }

    protected function createRecipients(Notification $notification, NotificationData $data): void 
    {

        $this->storeRecipients(
            $notification,
            $data->to,
            'to'
        );

        $this->storeRecipients(
            $notification,
            $data->cc ?? [],
            'cc'
        );

        $this->storeRecipients(
            $notification,
            $data->bcc ?? [],
            'bcc'
        );
    }

    protected function storeRecipients(Notification $notification, array $recipients, string $type): void 
    {

        foreach ($recipients as $recipient) {

            /*
             * Recipient may be:
             *
             * "john@test.com"
             *
             * OR
             *
             * [
             *     'email' => 'john@test.com',
             *     'name'  => 'John Smith',
             * ]
             */

            

            if (\is_string($recipient)) {

                NotificationRecipient::create([

                    'notification_id' => $notification->id,

                    'type' => $type,

                    'email' => $recipient,

                    'name' => null,

                ]);

                continue;
            }

            NotificationRecipient::create([

                'notification_id' => $notification->id,

                'type' => $type,

                'email' => $recipient['email'],

                'name' => $recipient['name'] ?? null,

            ]);
        }
    }

    protected function storeAttachments(Notification $notification, NotificationData $data): void
    {
        foreach ($data->attachments as $attachment) {

            if (! Storage::disk($attachment->disk)->exists($attachment->path)) {

                throw new AttachmentNotFoundException(
                    $attachment->path
                );

            }

            NotificationAttachment::create([

                'notification_id' => $notification->id,

                'disk' => $attachment->disk,

                'path' => $attachment->path,

                'filename' => $attachment->filename
                    ?? basename($attachment->path),

                'mime_type' => Storage::disk($attachment->disk)
                    ->mimeType($attachment->path),

                'size' => Storage::disk($attachment->disk)
                    ->size($attachment->path),

            ]);

        }
    }    

}
