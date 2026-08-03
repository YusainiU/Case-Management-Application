<?php

namespace Simplydigital\NotificationManagement\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    protected $fillable = [
        'notification_id',
        'type',
        'name',
        'email',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}