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
        $prefix = config('casemanagement.CUSTOMER_CONTACT_PREFIX');
        static::creating(function ($model) use ($prefix) {
            $model->number = "$prefix-" . now()->format('YmdHis') . mt_rand(1000, 9999);
        });
    }

    protected function casts()
    {
        return [
            'customerRole' => CustomerContactRoles::class,
        ];
    }

}
