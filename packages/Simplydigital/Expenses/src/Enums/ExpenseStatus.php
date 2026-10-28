<?php

namespace Simplydigital\Expenses\Enums;

enum ExpenseStatus: string
{
    case AwaitingApproval = 'awaiting_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RejectedAndReapply = 'rejected_and_reapply';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingApproval => 'Awaiting approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::RejectedAndReapply => 'Rejected and reapply',
        };
    }

    public function canBeReviewed(): bool
    {
        return in_array($this, [
            self::AwaitingApproval,
            self::RejectedAndReapply,
        ], true);
    }
}
