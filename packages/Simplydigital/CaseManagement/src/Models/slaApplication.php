<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;

class slaApplication extends Model
{
    protected $fillable = [
        'name',
        'number',
        'short_description',
        'customer_id',
        'service_level_agreement_id',
        'priority',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $prefix = config('case-management.SLA_APPLICATION_PREFIX');
        static::creating(function ($slaApplication) use ($prefix) {
            $slaApplication->number = "$prefix-" . str_pad($slaApplication->id, 6, '0', STR_PAD_LEFT);
        });
    }
    

}
