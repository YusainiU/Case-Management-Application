<?php

namespace Simplydigital\CaseManagement\Models;

use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Illuminate\Database\Eloquent\Model;

class CustomerContacts extends Model
{
    protected $fillable = [
        'user_id',
        'number',
        'customerRole',
        'description',        
    ];

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_CONTACT_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    protected function casts()
    {
        return [
            'customerRole' => CustomerContactRoles::class,
        ];
    }

}
