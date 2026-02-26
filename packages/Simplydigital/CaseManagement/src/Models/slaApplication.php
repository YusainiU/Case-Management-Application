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
        
        static::creating(function ($slaApplication) {
            $prefix = config('case-management.SLA_APPLICATION_PREFIX');
            $utilityService = app('case-management-utility-services');
            $slaApplication->number = $utilityService::generateRecordNumber($prefix);
        });
    }
    

}
