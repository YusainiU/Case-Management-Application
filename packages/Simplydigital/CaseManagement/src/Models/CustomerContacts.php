<?php

namespace Simplydigital\CaseManagement\Models;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContacts extends Model
{
    protected $fillable = [
        'user_id',
        'number',
        'customerRole',
        'description',
        'active',
        'has_portal_access',        
    ];

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_CONTACT_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    public function userDetails()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    protected function casts()
    {
        return [
            'customerRole' => CustomerContactRoles::class,
            'active' => 'boolean',
            'has_portal_access' => 'boolean',
        ];
    }

}
