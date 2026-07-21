<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Enums\slaAppPriorities;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;

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
            'priority' => slaAppPriorities::class,
        ];
    }

    public function serviceLevelAgreement()
    {
        return $this->belongsTo(serviceLevelAgreements::class, 'service_level_agreement_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    protected static function booted(): void
    {
        
        static::creating(function ($slaApplication) {
            $prefix = config('case-management.SLA_APPLICATION_PREFIX');
            $slaApplication->number = CaseManagementUtilityServices::generateNumber($prefix, slaApplication::class);
        });
    }
    

}
