<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
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
        "visible_to_external",
    ];

    protected static function booted()
    {       
        static::creating(function ($model) {
            $prefix = config('case-management.LOG_PREFIX');    
            $model->number = CaseManagementUtilityServices::generateNumber($prefix, TaskLogs::class);
        });

    }

    /**
     * Render historic AI engineer proposal logs with a human-readable name.
     * The stored audit description stays unchanged; account deletion still
     * leaves an explicit, identifiable legacy reference.
     */
    public function getHumanReadableDescriptionAttribute(): ?string
    {
        $description = $this->description;
        if (! is_string($description) || $this->source !== 'ai_agent'
            || ! str_contains($description, 'Proposed engineer user ID:')) {
            return $description;
        }

        return preg_replace_callback(
            '/Proposed engineer user ID:\\s*(\\d+)/',
            static function (array $match): string {
                $name = User::query()->whereKey((int) $match[1])->value('name');

                return $name
                    ? 'Proposed engineer: '.$name
                    : 'Proposed engineer: Former user (ID '.$match[1].')';
            },
            $description
        );
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