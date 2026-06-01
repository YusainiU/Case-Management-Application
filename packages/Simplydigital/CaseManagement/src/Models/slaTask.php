<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Simplydigital\CaseManagement\Services\UI;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class slaTask extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $warningTimerCount = 3;

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

    protected function getWarningTimer()
    {
        return config('case-management.CLOSE_TO_BREACH_WARNING');
    }

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
        return $this->belongsTo(slaApplication::class, 'sla_applications_id');
    }

    /**
     * Below are SLA status helper methods and relationships for pauses.
     * These are used to determine if the SLA is currently paused, breached,
     * and to calculate severity for UI indicators.
     */

    /**
     * Example usage
     * $task->paused;
     * $task->breached;
     * $breached = SlaTask::breached()->get();
     * $task->showWarningFlag;
     * $task->slaSeverity;
        switch (task.slaSeverity) {
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
    public function getPausedAttribute(): bool
    {
        return $this->activePause()->exists();
    }

    public function scopeNotPaused(Builder $query): Builder
    {
        return $query->whereDoesntHave('activePause');
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->whereHas('activePause');
    }

    /**
     * Has SLA breached?
     */
    public function getBreachedAttribute(): bool
    {
        if ($this->paused) {
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
            ->notPaused()
            ->where(function (Builder $query) {

                // Response SLA breached
                $query->where(function (Builder $query) {
                    $query->whereNotNull('respond_by')
                        ->where('respond_by', '<', now())
                        ->where(function (Builder $query) {
                            $query->whereNull('responded_at')
                                ->orWhereColumn('responded_at', '>', 'respond_by');
                        });
                })

                // OR Fix SLA breached
                    ->orWhere(function (Builder $query) {
                        $query->whereNotNull('fix_by')
                            ->where('fix_by', '<', now())
                            ->where(function (Builder $query) {
                                $query->whereNull('fixed_at')
                                    ->orWhereColumn('fixed_at', '>', 'fix_by');
                            });
                    });
            });
    }

    public function scopeNotBreached(Builder $query): Builder
    {
        return $query->whereNot(function (Builder $query) {
            $query->breached();
        });
    }

    public function scopeFixBreached(Builder $query): Builder
    {
        return $query
            ->notPaused()
            // OR Fix SLA breached
            ->orWhere(function (Builder $query) {
                $query->whereNotNull('fix_by')
                    ->where('fix_by', '<', now())
                    ->where(function (Builder $query) {
                        $query->whereNull('fixed_at')
                            ->orWhereColumn('fixed_at', '>', 'fix_by');
                    });
            });
    }

    public function getShowWarningFlagAttribute(): bool
    {
        if (
            $this->responded_at !== null ||
            $this->paused ||
            $this->breached ||
            $this->respond_by === null
        ) {
            return false;
        }

        return now()->diffInHours($this->respond_by, false) <= $this->getWarningTimer()
            && $this->respond_by->isFuture();
    }

    public function scopeShowWarningFlag(Builder $query): Builder
    {
        return $query
            ->whereNull('responded_at')
            ->notPaused()
            ->notBreached()
            ->whereNotNull('respond_by')
            ->whereBetween('respond_by', [
                now(),
                now()->copy()->addHours($this->getWarningTimer()),
            ]);
    }

    public function getSlaSeverityAttribute(): ?string
    {
        if ($this->breached) {
            return null;
        }

        if ($this->activePause) {
            return null;
        }

        $minutes = now()->diffInMinutes(
            $this->respond_by,
            false
        );

        return match (true) {
            $minutes <= 60 => UI::CRITICAL_STATE_TEXT,
            $minutes <= 180 => UI::WARNING_STATE_TEXT,
            $minutes <= 360 => UI::CAUTION_STATE_TEXT,
            default => null,
        };
    }

    public function getSlaFlagAttribute(): ?string
    {
        $flag = UI::DEFAULT_STATE_CLASS;
        if ($this->breached) {
            $flag = UI::BREACHED_STATE_CLASS;
        }
        if ($this->sla_severity) {
            $flag = UI::CLOSE_TO_BREACH_CLASS;
        }

        return $flag;
    }

    public function getSlaFlagTextAttribute(): ?string
    {
        $flag = 'Normal';
        if ($this->breached) {
            $flag = UI::BREACHED_STATE_TEXT;
        }
        if ($this->sla_severity) {
            $flag = $this->sla_severity;
        }

        return $flag;
    }

    public function getSlaStatusInfoAttribute(): ?string
    {

        $hours = now()->diffInHours($this->respond_by, false);
        $rounded = round($hours,2);
        return $hours >= 0
            ? "{$rounded} hours remaining"
            : abs($rounded).' hours overdue';

    }
}
