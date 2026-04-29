<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'short_description',
        'number',
        'product_id',
        'supplier_id',
        'customer_id',
        'active',
        'buy_price',
        'sell_price',
        'notes',
        'license_number',
        'location',
        'technical_specifications',
        'mac_address',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
        ];   
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.ASSET_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }    

    public function getBuyPriceFormattedAttribute($value)
    {
        return '£' . number_format(floatval($this->buy_price), 2);
    }

    public function getSellPriceFormattedAttribute($value)
    {
        return '£' . number_format(floatval($this->sell_price), 2);
    }

    /*
        Example usage:
        $asset = new Asset();
        $asset->buy_price = 123.45;
        echo $asset->getBuyPriceFormattedAttribute(); // Output: £123.45
    */

        
}

