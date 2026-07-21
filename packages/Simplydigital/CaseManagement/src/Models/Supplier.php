<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;

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
        'short_description',
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
            $supplier->number = CaseManagementUtilityServices::generateNumber($prefix, Supplier::class);
        });
    }
    
}
