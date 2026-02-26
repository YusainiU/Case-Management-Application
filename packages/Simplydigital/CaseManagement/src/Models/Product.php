<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected static function booted()
    {
        $prefix = config('case-management.PRODUCT_PREFIX');
        static::creating(function ($product) use ($prefix) {
            $product->number = "$prefix-" . str_pad($product->id, 6, '0', STR_PAD_LEFT);
        });
    }

}
