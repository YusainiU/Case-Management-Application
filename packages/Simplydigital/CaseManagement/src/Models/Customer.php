<?php

namespace Simplydigital\CaseManagement\Models;

use Simplydigital\CaseManagement\Enums\CustomerChildTypes;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'primary_type',
        'child_type',
        'url',
        'address_1',
        'address_2',
        'address_3',
        'telephone_1',
        'telephone_2',
        'telephone_3',
        'parent_company',
        'active',
        'portal_enabled',
    ];


    protected static function newFactory()
    {
        return \Simplydigital\CaseManagement\Database\Factories\CustomerFactory::new();
    }

    protected function casts(): array
    {
        return [
            'primary_type' => CustomerPrimaryTypes::class,
            'child_type' => CustomerChildTypes::class,
            'active' => 'boolean',
            'portal_enabled' => 'boolean',
        ];
    }

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }
    
    public function parentCompany()
    {
        return $this->belongsTo(Customer::class,'parent_company');
    }

    public function getAddress()
    {
        if($this->address_1) {
            $address = [
                $this->address_1,
                $this->address_2,
                $this->address_3,
            ];
            return $address;
        }
        return [];
    }    
    
}
