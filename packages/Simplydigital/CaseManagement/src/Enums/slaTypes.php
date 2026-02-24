<?php

namespace Simplydigital\CaseManagement\Enums;

enum slaTypes: string
{
    case Support = 'support';
    case Services = 'services';

    public function label(): string
    {
        return match($this) {
            self::Support => 'Support',
            self::Services => 'Services',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function isSupport(): bool
    {
        return $this === self::Support;
    }

    public function isServices(): bool
    {
        return $this === self::Services;
    }

}