<?php

namespace Simplydigital\UserManagement\Model;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\Activitylog\Models\Concerns\LogsActivity; 
use Spatie\Activitylog\Support\LogOptions;

class UsersDetails extends Model
{
    use LogsActivity;

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
        'identification',   //customer/internal/other
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ticket')
            ->logAll()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly([]);
    }

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
