<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'telephone',
        'email',
        'url',
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
        
        static::creating(function (self $supplier) {
            $prefix = config('case-management.SUPPLIER_PREFIX');
            $utilityService = app('case-management-utility-services');
            $supplier->number = $utilityService::generateRecordNumber($prefix);
        });
    }
    
}
