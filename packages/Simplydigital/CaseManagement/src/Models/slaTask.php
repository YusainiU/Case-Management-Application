<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class slaTask extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'number',
        'name',
        'short_description',
        'task_type',
        'ticket_id',
        'sla_applications_id',
        'respond_by',
        'fix_by',
        'responded_at',
        'fixed_at',
        'completed_at',
        'paused_at',
        'resumed_at',
        'breached_at',
        'active',
    ];

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ticket')
            ->logAll()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly([]);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'responded_at' => 'datetime',
            'fixed_at' => 'datetime',
            'completed_at' => 'datetime',
            'paused_at' => 'datetime',
            'resumed_at' => 'datetime',
            'breached_at' => 'datetime',
            'respond_by' => 'datetime',
            'fix_by' => 'datetime',
        ];
    }

    protected static function booted(): void
    {

        static::creating(function ($slaTask) {
            $prefix = config('case-management.SLA_TASK_PREFIX');
            $utilityService = app('case-management-utility-services');
            $slaTask->number = $utilityService::generateRecordNumber($prefix);

        });
    }

    // / Relationships

    public function caseTicket()
    {
        return $this->belongsTo(tickets::class, 'ticket_id');
    }

    public function slaApplication()
    {
        return $this->belongsTo(SlaApplication::class, 'sla_applications_id');
    }

    /**
     * Below are SLA status helper methods and relationships for pauses.
     * These are used to determine if the SLA is currently paused, breached,
     * and to calculate severity for UI indicators.
     */

    /**
     * Example usage
     * $task->is_paused;
     * $task->is_breached;
     * $breached = SlaTask::breached()->get();
     * $task->show_warning_flag;
     * $task->sla_severity;
        switch (task.sla_severity) {
            case 'critical':
                // red pulse
                break;

            case 'warning':
                // orange pulse
                break;

            case 'caution':
                // yellow
                break;

            case 'breached':
                // solid red
                break;
        }
     */

    /**
     * All pause periods
     */
    public function pauses()
    {
        return $this->hasMany(SlaTaskPause::class);
    }

    /**
     * Current active pause
     */
    public function activePause()
    {
        return $this->hasOne(SlaTaskPause::class)->whereNull('resumed_at');
    }

    /**
     * Is SLA currently paused?
     */
    public function getIsPausedAttribute(): bool
    {
        return $this->activePause()->exists();
    }

    /**
     * Has SLA breached?
     */
    public function getIsBreachedAttribute(): bool
    {
        if ($this->is_paused) {
            return false;
        }

        $responseBreached =
            $this->respond_by !== null
            && $this->respond_by->isPast()
            && (
                $this->responded_at === null
                || $this->responded_at->gt($this->respond_by)
            );

        $fixBreached =
            $this->fix_by !== null
            && $this->fix_by->isPast()
            && (
                $this->fixed_at === null
                || $this->fixed_at->gt($this->fix_by)
            );

        return $responseBreached || $fixBreached;
    }

    /**
     * Scope: breached tasks
     */
    public function scopeBreached(Builder $query): Builder
    {
        return $query
            ->whereNull('responded_at')
            ->where('respond_by', '<', now())
            ->whereDoesntHave('activePause');
    }

    public function getShowWarningFlagAttribute(): bool
    {
        if (
            $this->responded_at !== null ||
            $this->is_paused ||
            $this->is_breached ||
            $this->respond_by === null
        ) {
            return false;
        }

        return now()->diffInHours($this->respond_by, false) <= 3
            && $this->respond_by->isFuture();
    }

    public function getSlaSeverityAttribute(): string
    {
        if ($this->is_breached) {
            return 'breached';
        }

        if ($this->is_paused) {
            return 'paused';
        }

        $minutes = now()->diffInMinutes(
            $this->respond_by,
            false
        );

        return match (true) {
            $minutes <= 60 => 'critical',
            $minutes <= 180 => 'warning',
            $minutes <= 360 => 'caution',
            default => 'normal',
        };
    }
}
