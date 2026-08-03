<?php

namespace Simplydigital\NotificationManagement\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttempts extends Model
{

    protected $fillable = [
        'notification_id',
        'attempt',
        'attempted_at',
        'status',
        'transport',
        'message_id',
        'response',
        'exception',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
        ];
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}