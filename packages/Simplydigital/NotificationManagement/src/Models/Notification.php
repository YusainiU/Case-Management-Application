<?php

namespace Simplydigital\NotificationManagement\Models;

use App\Models\User;
use Throwable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Simplydigital\NotificationManagement\Notifications\Events\{
    NotificationSent,
    NotificationFailed,
    NotificationSending,
};
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationResult;
use Simplydigital\NotificationManagement\Enums\{
    NotificationPriority,
    NotificationStatus,
    NotificationTemplate,
    NotificationChannel,
};

class Notification extends Model
{
    protected $fillable = [
        'uuid',
        'subject',
        'channel',
        'status',
        'priority',
        'template',
        'trigger',
        'view_data',
        'created_by',
        'queued_at',
        'sent_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'template' => NotificationTemplate::class,
            'status' => NotificationStatus::class,
            'priority' => NotificationPriority::class,
            'channel' => NotificationChannel::class,
            'view_data' => 'array',
        ];
    }

    public function notificationRecipient()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function attachments()
    {
        return $this->hasMany(NotificationAttachment::class);
    }    

    public function notificationAttempts()
    {
        return $this->hasMany(NotificationAttempts::class);
    }

    public function notificationRecipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    /**
     * Get the template view path safely
     */
    public function getTemplateView(): string
    {
        // This tells the IDE we know it's an enum
        $template = $this->template;
        if ($template instanceof NotificationTemplate) {
            return $template->view();
        }
        
        // Fallback if not cast properly
        return (string) $template;
    }
    
    /**
     * Get the template with fallback
     */
    public function getTemplateValue(): string
    {
        return $this->template instanceof NotificationTemplate 
            ? $this->template->value 
            : (string) $this->template;
    }    

    /*
    |--------------------------------------------------------------------------
    | Example of creating polymorphic relationshipt
    |--------------------------------------------------------------------------

        public function __construct(
            // ... other properties
            public Model $trigger,  // Accept any Eloquent model for example Order::class
            // ... other properties
        ) {}

        // When creating the notification:
        $notification = Notification::create([
            'subject' => 'Order Confirmed',
            'trigger_id' => $order->id,        // The ID
            'trigger_type' => get_class($order), // 'App\Models\Order'
            'created_by' => auth()->id(),
            // ... other fields
        ]);

    |--------------------------------------------------------------------------
    | The function trigger()
    |--------------------------------------------------------------------------
        When $notification->trigger is called, Laravel ...
            - Reads the trigger_type column (e.g., 'App\Models\Order')
            - Reads the trigger_id column (e.g., 5)
            - Automatically queries the correct model: Order::find(5)
            - Returns the model instance
    */



    public function trigger()
    {
        return $this->morphTo();
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markSent(NotificationResult $result): void
    {
        $this->update([

            'status'      => NotificationStatus::Sent,

            'sent_at'     => now(),

            'transport'   => $result->transport,

            'message_id'  => $result->messageId,

        ]);

        NotificationSent::dispatch($this);
    }

    public function markFailed(Throwable $exception): void 
    {

        $this->update([

            'status' => NotificationStatus::Failed,

            'failed_at' => now(),

            'error_message' => $exception->getMessage(),

        ]);

        NotificationFailed::dispatch(
            $this,
            $exception
        );
    }
    
    public function markSending(): void
    {
        $this->update([

            'status' => NotificationStatus::Sending,

        ]);

        NotificationSending::dispatch($this);
    }

    /**
     * Get recipients by type with email and name
     */
    public function getRecipientsByType(string $type): Collection
    {
        return $this->notificationRecipients
            ->where('type', $type)
            ->whereNotNull('email')
            ->mapWithKeys(function ($recipient) {
                return [$recipient->email => $recipient->name ?? null];
            });
    }

    /**
     * Format recipients for Mail::to(), ::cc(), ::bcc()
     */
    protected function formatRecipientsForMail(Collection $recipients)
    {
        return $recipients
            ->map(function ($name, $email) {
                return $name ? "{$name} <{$email}>" : $email;
            })
            ->values();
    }    
    
    public function toRecipients(): array
    {
        $em = $this->getRecipientsByType('to');
        $email = $em->keys()->first();
        $name = $em[$email];
        $result = ['email' => $email, 'name' => $name];
        return $result;
    }

    public function ccRecipients()
    {
        $results = [];
        $em = $this->getRecipientsByType('cc');
        foreach($em as $k => $v) {
            $results[] = $k;
        }
        return $results;
    }

    public function bccRecipients(): array
    {
        $results = [];
        $em = $this->getRecipientsByType('bcc');
        foreach($em as $k => $v) {
            $results[] = $k;
        }
        return $results;        
    }

}
