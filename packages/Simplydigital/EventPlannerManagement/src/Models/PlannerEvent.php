<?php

namespace Simplydigital\EventPlannerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;


class PlannerEvent extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'starts_at',
        'ends_at',
        'all_day',
        'colour',
        'url',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }    

}
