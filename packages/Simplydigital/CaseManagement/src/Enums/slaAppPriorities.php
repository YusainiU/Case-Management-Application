<?php

namespace Simplydigital\CaseManagement\Enums;

enum slaAppPriorities: int
{
    case Urgent = 1;
    case NotUrgent = 2;

    public function label(): string
    {
        return match($this) {
            self::Urgent => 'Urgent',
            self::NotUrgent => 'Not Urgent',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function isUrgent(): bool
    {
        return $this === self::Urgent;
    }

    public function isNotUrgent(): bool
    {
        return $this === self::NotUrgent;
    }

}