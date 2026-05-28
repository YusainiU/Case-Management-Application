<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;

class SlaTaskPause extends Model
{
    protected $table = 'sla_task_pauses';

    protected $casts = [
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
    ];

    protected $fillable = [
        'sla_task_id',
        'paused_at',
        'resumed_at',
    ];

    public function slaTask()
    {
        return $this->belongsTo(SlaTask::class);
    }

    /**
     * Is this pause currently active?
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->paused_at !== null
            && $this->resumed_at === null;
    }
}