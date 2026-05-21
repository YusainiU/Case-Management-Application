<?php

namespace Simplydigital\CaseManagement\Enums;

enum CaseState: string
{
    case Completed = 'Completed';
    case Paused = 'Paused';
    case In_Progress = 'In Progress';
    case Open = 'Open';
    case Resolved = 'Resolved';
    case Cancelled = 'Cancelled';
    case Closed = 'Closed';

    public function label(): string
    {
        return match($this) {
            self::Completed => 'Completed',
            self::Paused => 'Paused',
            self::In_Progress => 'In Progress',
            self::Open => 'Open',
            self::Resolved => 'Resolved',
            self::Cancelled => 'Cancelled',
            self::Closed => 'Closed',
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }
    
    public static function openStates(): array
    {
        return [
            self::Open,
            self::In_Progress,
            self::Paused,
        ];
    }

    public static function closedStates(): array
    {
        return [
            self::Completed,
            self::Resolved,
            self::Cancelled,
            self::Closed,
        ];
    }
    
    public static function isClosed(string $state): bool
    {
        return \in_array(self::from($state), self::closedStates());
    }

    public static function isOpen(string $state): bool
    {
        return \in_array(self::from($state), self::openStates());
    }

    public static function isCompleted(string $state): bool
    {
        return self::from($state) === self::Completed;
    }

    public static function isPaused(string $state): bool
    {
        return self::from($state) === self::Paused;
    }

    public static function isInProgress(string $state): bool
    {
        return self::from($state) === self::In_Progress;
    }

    public static function isResolved(string $state): bool
    {
        return self::from($state) === self::Resolved;
    }

    public static function isOpenState(string $state): bool
    {
        return self::from($state) === self::Open;
    }

    public static function isCancelled(string $state): bool
    {
        return self::from($state) === self::Cancelled;
    }

    public static function isClosedCase(string $state): bool
    {
        return self::from($state) === self::Closed;
    }

    public function color(): string
    {
        return match($this) {
            self::Completed => 'green',
            self::Paused => 'yellow',
            self::In_Progress => 'blue',
            self::Open => 'gray',
            self::Resolved => 'green',
            self::Cancelled => 'red',
            self::Closed => 'black',
        };
    }

}