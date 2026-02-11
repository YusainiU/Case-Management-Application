<?php

Namespace Simplydigital\UserManagement\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
class UsersStatuses extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}