<?php

namespace App\Models;

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

    protected static function booted()
    {
        $prefix = config('casemanagement.ASSET_PREFIX');
        static::creating(function ($model) use ($prefix) {
            $model->number = "$prefix-" . now()->format('YmdHis') . mt_rand(1000, 9999);
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

