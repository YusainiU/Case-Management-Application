<?php

namespace Simplydigital\StockManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\StockManagement\Enums\StockRequestStatus;

class StockRequestUpdate extends Model
{
    protected $fillable = [
        'user_id',
        'from_status',
        'to_status',
        'notes',
        'task_log_id',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => StockRequestStatus::class,
            'to_status' => StockRequestStatus::class,
        ];
    }

    public function stockRequest(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskLog(): BelongsTo
    {
        return $this->belongsTo(TaskLogs::class, 'task_log_id');
    }
}
