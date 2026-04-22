<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Enums\ProductTypes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_code',
        'name',
        'product_code',
        'short_description',
        'model',
        'type',
        'make',
        'version',
        'active',
    ];

    protected static function newFactory()
    {
        return \Simplydigital\CaseManagement\Database\Factories\ProductFactory::new();
    }
    
    protected static function booted()
    {      
        static::creating(function ($product) {
            $prefix = config('case-management.PRODUCT_PREFIX');
            $utilityService = app('case-management-utility-services');
            $product->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    public function casts(): array
    {
        return [
            'active' => 'boolean',
            'type' => ProductTypes::class,
        ];
    }
}
