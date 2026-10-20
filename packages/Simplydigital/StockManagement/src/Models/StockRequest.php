<?php

namespace Simplydigital\StockManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Simplydigital\CaseManagement\Models\Product;
use Simplydigital\CaseManagement\Models\Supplier;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\StockManagement\Enums\StockRequestStatus;

class StockRequest extends Model
{
    protected $fillable = [
        'number',
        'case_id',
        'product_id',
        'supplier_id',
        'requested_by',
        'part_description',
        'quantity',
        'notes',
        'status',
        'supplier_reference',
        'tracking_reference',
    ];

    protected $attributes = [
        'status' => StockRequestStatus::Requested->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => StockRequestStatus::class,
            'quantity' => 'integer',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(tickets::class, 'case_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(StockRequestUpdate::class)->oldest();
    }
}
