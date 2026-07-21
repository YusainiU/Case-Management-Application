<?php

namespace Simplydigital\CaseManagement\Models;

use App\CustomerContacts as AppCustomerContacts;
use App\Models\User;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\UserManagement\Model\UsersDetails;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;

class CustomerContacts extends Model
{
    protected $fillable = [
        'user_id',
        'number',
        'customer_id',
        'customerRole',
        'description',
        'active',
        'has_portal_access',        
    ];

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_CONTACT_PREFIX');
            $model->number = CaseManagementUtilityServices::generateNumber($prefix, CustomerContacts::class);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function customerUser()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function customerUserDetails()
    {
        return $this->hasOneThrough(UsersDetails::class, User::class, 'id', 'user_id', 'user_id', 'id');
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
