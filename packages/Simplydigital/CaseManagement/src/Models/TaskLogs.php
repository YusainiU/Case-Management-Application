<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class TaskLogs extends Model
{
    use SoftDeletes;
    protected $fillable  = [
        "number",
	    "name",
        "short_description",
        "type",                    
        "source",                              
	    "user_id",
        "external_user",
        "sla_tasks_id",
        "ticket_id",
        "description",
        "notification_sent_at",
        "require_attention",
        "task_site_visit_id",
        "response_to_external_comment",
        "response_to_internal_comment",
    ];

    protected static function booted()
    {       
        static::creating(function ($model) {
            $prefix = config('case-management.LOG_PREFIX');    
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    public function getUserNameAttribute()
    {
        $u = $this->user()->first();
        return $u->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(tickets::class, 'ticket_id');
    }

    public function isInternal()
    {
        return $this->user_id !== null;
    }

    public function getLogCreatorAttribute()
    {
        if($this->user_id) {
            return $this->user()->first()->name;
        }else{
            return $this->external_user;
        }
    }

    public function logNumber(string $value)
    {
        return $this->where('number','=',$value)->first();
    }

    public function slaTask()
    {
        return $this->belongsTo(TaskLogs::class,'task_log_id');
    }

    public function scopeRequiringAttention(Builder $query): Builder
    {
        return $query->where('require_attention', true);
    }

    public function parentInternalComment()
    {
        return $this->belongsTo(TaskLogs::class, 'response_to_internal_comment');
    }

    public function repliesToInternalComment()
    {
        return $this->hasMany(TaskLogs::class, 'response_to_internal_comment');        
    }

    public function checkInternalRepliesExist()
    {
        return $this->repliesToInternalComment()->exists();
    }

    public function parentExternalComment()
    {
        return $this->belongsTo(TaskLogs::class, 'response_to_external_comment');
    }

    public function repliesToExternalComment()
    {
        return $this->hasMany(TaskLogs::class, 'response_to_external_comment');
    }

    public function checkExternalRepliseExist()
    {
        return $this->repliesToExternalComment()->exists();
    }

    public function scopeUnrespondedComment(Builder $query)
    {
        return $query->where('source','=','external')
            ->where('response_to_external_comment','=',null)
            ->whereRelation('ticket','closed_datetime','=',null);
    }
        
}