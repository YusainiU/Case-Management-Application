<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity; 
use Spatie\Activitylog\Support\LogOptions;

class tickets extends Model
{

    use SoftDeletes;
    use LogsActivity;

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ticket')
            ->logAll()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly([]);
    }
    protected $fillable = [
        'number',
        'customer_id',
        'sla_applications_id',
        'customer_reference',
        'category',
        'created_by',
        'source',
        'list_of_assets',
        'short_description',
        'description',
        'state',
        'raised_by_user',
        'raised_by_nonuser',
        'contact_telephone',
        'contact_email',
        'assigned_to',
        'assigned_group',
        'currently_working',
        'working_time',
        'resolution',
        'resolution_details',
        'closed_datetime',
        'fixed_datetime',
        'resolved_datetime',
        'resolved_by',
    ];

    protected static function booted()
    {

        static::creating(function ($model) {
            $prefix = config('case-management.TICKET_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    protected function casts()
    {
        return [
            'currently_working' => 'boolean',
        ];
    }
}
