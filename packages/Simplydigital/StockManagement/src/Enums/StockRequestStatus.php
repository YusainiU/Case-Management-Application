<?php

namespace Simplydigital\StockManagement\Enums;

enum StockRequestStatus: string
{
    case Requested = 'requested';
    case Ordered = 'ordered';
    case Dispatched = 'dispatched';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Ordered => 'Ordered',
            self::Dispatched => 'Dispatched',
            self::Received => 'Received / Verified',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Requested => [self::Ordered, self::Cancelled],
            self::Ordered => [self::Dispatched, self::Cancelled],
            self::Dispatched => [self::Received, self::Cancelled],
            self::Received, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }
}
