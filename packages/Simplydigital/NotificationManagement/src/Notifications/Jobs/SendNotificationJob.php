<?php

namespace Simplydigital\NotificationManagement\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\Models\NotificationRecipient;
use Simplydigital\NotificationManagement\Enums\NotificationChannel;
use Simplydigital\NotificationManagement\Enums\NotificationStatus;
use Simplydigital\NotificationManagement\Enums\NotificationTemplate;
use Simplydigital\NotificationManagement\Exceptions\InvalidChannelException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
class SendNotificationJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;   // Job implementation goes here
    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1, 5, 10 minutes
    public $timeout = 120;
    
    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Notification $notification
    ) {}
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
       try {
            // Update status to processing
            $this->notification->update([
                'status' => NotificationStatus::Pending->value,
                'queued_at' => now(),
            ]);

            // Get the trigger model and recipient
            $trigger = $this->notification->trigger;
            $recipients = $this->notification->notificationRecipients;

            // Send based on channel
            $channel = NotificationChannel::tryFrom($this->notification->channel);
            
            if (!$channel) {
                throw new InvalidChannelException("Invalid channel: {$this->notification->channel}");
            }

            $result = match ($channel) {
                NotificationChannel::Email => $this->sendEmail($recipients, $trigger),
                // NotificationChannel::Sms => $this->sendSms($recipients, $trigger),
                // NotificationChannel::Push => $this->sendPush($recipients, $trigger),
                // NotificationChannel::Teams => $this->sendTeams($recipients, $trigger),
                // NotificationChannel::Slack => $this->sendSlack($recipients, $trigger),
                // NotificationChannel::WhatsApp => $this->sendWhatsApp($recipients, $trigger),
                // NotificationChannel::Webhook => $this->sendWebhook($recipients, $trigger),
                // NotificationChannel::Internal => $this->sendInternal($recipients, $trigger),
            };

            // Update status to sent
            $this->notification->update([
                'status' => NotificationStatus::Sent->value,
                'sent_at' => now(),
            ]);

            // Log successful send
            $this->logNotification('success', $result);

        } catch (\Exception $e) {
            // Update status to failed
            $this->notification->update([
                'status' => NotificationStatus::Failed->value,
                'failed_at' => now(),
            ]);

            // Log failure
            $this->logNotification('error', $e->getMessage());

            // Re-throw to trigger retry
            throw $e;
        }            
    }

    /**
     * Send email notification
     */
    protected function sendEmail(NotificationRecipient $recipients, Model $trigger): array
    {
        $emails = $recipients->pluck('email')->toArray();
        
        // Use Laravel's Mail facade
        Mail::send(
            $this->notification?->template ?? NotificationTemplate::EmailNotification->value,
            ['notification' => $this->notification, 'trigger' => $trigger],
            function ($message) use ($emails) {
                $message->to($emails)
                    ->subject($this->notification->subject);
            }
        );

        return ['channel' => 'email', 'recipients' => \count($emails)];
    }
    
    /**
     * Log notification activity
     */
    protected function logNotification(string $level, mixed $message): void
    {
        Log::{$level}('Notification processed', [
            'notification_id' => $this->notification->id,
            'channel' => $this->notification->channel,
            'subject' => $this->notification->subject,
            'message' => $message,
            'attempts' => $this->attempts(),
        ]);
    }    


}