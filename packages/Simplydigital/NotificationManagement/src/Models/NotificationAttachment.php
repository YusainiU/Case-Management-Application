<?php

namespace Simplydigital\NotificationManagement\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttachment extends Model
{
    protected $fillable = [

        'notification_id',

        'disk',

        'path',

        'filename',

        'mime_type',

        'size',

    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}