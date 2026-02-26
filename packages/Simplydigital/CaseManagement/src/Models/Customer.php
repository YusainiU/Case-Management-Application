<?php

namespace Simplydigital\CaseManagement\Models;

use Simplydigital\CaseManagement\Enums\CustomerChildTypes;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $prefix = config('casemanagement.CUSTOMER_PREFIX');
        static::creating(function ($model) use ($prefix) {
            $model->number = "$prefix-" . now()->format('YmdHis') . mt_rand(1000, 9999);
        });
    }      
    
}
