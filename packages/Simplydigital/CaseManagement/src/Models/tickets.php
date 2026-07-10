<?php

namespace Simplydigital\CaseManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity; 
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;

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

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class,'subject');        
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

    public function slaApplication()
    {
        return $this->belongsTo(slaApplication::class, 'sla_applications_id');
    }

    public function raisedByUser()
    {
        return $this->belongsTo(CustomerContacts::class, 'raised_by_user', 'user_id');
    }

    public function slaTasks()
    {
        return $this->hasOne(slaTask::class, 'ticket_id');
    }

    public function taskLogs()
    {
        return $this->hasMany(TaskLogs::class, 'ticket_id');
    }

    public function requiresAttention(): bool
    {
        return $this->taskLogs()->where('require_attention', true)->exists();
    }
    
    public function getHasInternalUpdateAttribute()
    {
        return $this->taskLogs()->where('type', 'internal_update_log')->exists();
    }

    public function latestInternalUpdate()
    {
        return $this->hasOne(TaskLogs::class)
            ->where('type', 'internal_update')
            ->latestOfMany();
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getUnassignedAttribute()
    {
        return $this->assigned_to === null;
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getIsResolvedAttribute()
    {
        return $this->resolved_datetime !== null;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

     public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function siteVisits()
    {
        return $this->hasMany(TaskSiteVisit::class, 'ticket_id');
    }

    public function outstandingVisit()
    {
        return $this->hasMany(TaskSiteVisit::class, 'ticket_id')
            ->where('active',true)
            ->whereNull('offsite_at');        
    }

    public function scopeOpenCases(Builder $query): Builder
    {
        return $query->whereNull('closed_datetime');
    }

    protected function casts()
    {
        return [
            'currently_working' => 'boolean',
            'category' => CaseCategory::class,
            'source' => CaseSource::class,
            'state' => CaseState::class,
            'list_of_assets' => 'array',
        ];
    }
}
