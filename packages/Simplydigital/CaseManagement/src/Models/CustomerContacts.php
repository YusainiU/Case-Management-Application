<?php

namespace Simplydigital\CaseManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Database\Factories\CustomerContactFactory;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\UserManagement\Model\UsersDetails;

class CustomerContacts extends Model
{
    /** @use HasFactory<CustomerContactFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'number',
        'customer_id',
        'customerRole',
        'description',
        'active',
        'has_portal_access',
    ];

    protected static function newFactory()
    {
        return CustomerContactFactory::new();
    }

    protected static function booted()
    {

        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_CONTACT_PREFIX');
            $model->number = CaseManagementUtilityServices::generateNumber($prefix, CustomerContacts::class);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function customerUser()
    {
        return $this->belongsTo(User::class, 'user_id');
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
