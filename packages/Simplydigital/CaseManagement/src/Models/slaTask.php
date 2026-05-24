<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity; 
use Spatie\Activitylog\Support\LogOptions;

class slaTask extends Model
{
    use SoftDeletes;
    use LogsActivity;
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


}
