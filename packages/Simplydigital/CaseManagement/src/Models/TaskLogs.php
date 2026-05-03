<?php

namespace Simplydigital\CaseManagement\Models;

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
    ];

    protected static function booted()
    {       
        static::creating(function ($model) {
            $prefix = config('case-management.LOG_PREFIX');    
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    public function getUserIdAttribute($value)
    {
        if($value) {
            $u = User::find($value,['name']);
            return $u->name;
        }
        return null;
    }

    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(tickets::class, 'ticket_id');
    }
   public function internalUser()
    {
        $u = $this->attributes()['user_id'];
        return User::find($u)->get();
    }
    public function logNumber($value)
    {
        return $this->where('number','=',$value)->first();
    }

    public function scopeUnrespondedComment($query)
    {
        return $query->where('source','=','external')
            ->where('response_to_external_comment','=',null)
            ->whereRelation('ticket','closed_datetime','=',null);
    }
        
}