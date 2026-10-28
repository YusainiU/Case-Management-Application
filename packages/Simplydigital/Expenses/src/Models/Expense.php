<?php

namespace Simplydigital\Expenses\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\Expenses\Database\Factories\ExpenseFactory;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Enums\ExpenseType;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'case_id',
        'user_id',
        'week_number',
        'week_year',
        'task_date',
        'start_time',
        'end_time',
        'type',
        'description',
        'receipt_path',
        'amount',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $attributes = [
        'status' => ExpenseStatus::AwaitingApproval->value,
    ];

    protected function casts(): array
    {
        return [
            'task_date' => 'date',
            'type' => ExpenseType::class,
            'status' => ExpenseStatus::class,
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(tickets::class, 'case_id');
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
