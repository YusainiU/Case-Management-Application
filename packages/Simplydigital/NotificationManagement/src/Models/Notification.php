<?php

namespace Simplydigital\NotificationManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
}
