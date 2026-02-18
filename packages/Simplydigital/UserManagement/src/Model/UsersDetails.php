<?php

Namespace Simplydigital\UserManagement\Model;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class UsersDetails extends Model
{
    protected $fillable = [
        'user_id',
        'date_started',
        'phone',
        'address_1',
        'address_2',
        'county',
        'postcode',
        'country',
        'avatar',
    ];

    public function getAddress()
    {
        $address = [
            $this->address_1,
            $this->address_2,
            $this->county,
            $this->postcode,
            $this->country,
        ];
        return $address;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}