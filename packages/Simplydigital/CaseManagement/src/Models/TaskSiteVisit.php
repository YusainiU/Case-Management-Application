<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity; 
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Simplydigital\CaseManagement\Models\tickets;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TaskSiteVisit extends Model
{

    use SoftDeletes;
     use LogsActivity;
    protected $fillable = [
        'number',
        'short_description' ,
        'ticket_id' ,
        'assigned_to' ,
        'visit_scheduled_at' ,
        'first_visit' ,
        'scheduled_by' ,
        'description' ,
        'enroute_at' ,
        'onsite_at' ,
        'offsite_at',
        'active',
    ];

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('task_site_visit')
            ->logAll()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly([]);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class,'subject');        
    }
    
    public function visitScheduled()
    {
        return $this->visit_scheduled_at !== null;
    }

    public function visitEnroute()
    {
        return $this->enroute_at !== null;
    }

    public function visitOnsite()
    {
        return $this->onsite_at !== null;
    }

    public function visitOffsite()
    {
        return $this->offsite_at !== null;
    }

    public function casts(): array
    {
        return [
            'visit_scheduled_at' => 'datetime',
            'enroute_at' => 'datetime',
            'onsite_at' => 'datetime',
            'offsite_at' => 'datetime',
            'first_visit' => 'boolean',
            'active' => 'boolean',
        ];
    }

    protected static function booted()
    {       
        static::creating(function ($model) {
            $prefix = config('case-management.LOGVISIT_PREFIX');    
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }    
    
    public function ticket()
    {
        return $this->belongsTo(tickets::class, 'ticket_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function scopeVisitEvents(Builder $query): Builder
    {
        return $query->whereRelation('ticket','closed_datetime','=', null);
    }

    public function scopeSiteVisit(Builder $query): Builder
    {
        return $query->where('onsite_at','=', null)
                ->where('offsite_at','=',null)
                ->whereRelation('ticket','closed_datetime','=', null);
    }

    public function scopeCurrentlyOnsite(Builder $query): Builder
    {
        return $query->where('onsite_at','!=', null)
                ->where('offsite_at','=',null)
                ->where('enroute_at','!=', null)
                ->whereRelation('ticket','closed_datetime','=', null);
    }

    public function scopeCurrentlyEnroute(Builder $query): Builder
    {
        return $query->where('onsite_at','=',null)
                ->where('offsite_at','=', null)
                ->where('enroute_at','!=', null)
                ->whereRelation('ticket','closed_datetime','=',null);
    }
    
}
