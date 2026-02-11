<?php

Namespace Simplydigital\UserManagement\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
class UsersDetails extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'date_started',
        'phone',
        'address_1',
        'address_2',
        'county',
        'postcode',
        'country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}