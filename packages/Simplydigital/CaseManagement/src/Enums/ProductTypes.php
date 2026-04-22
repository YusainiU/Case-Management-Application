<?php

namespace Simplydigital\CaseManagement\Enums;

enum ProductTypes: string
{
    case Hardware = "hardware";
    case Software = "software";
    case Service = "service";
    case Network = "network";

    public function label(): string
    {
        return match($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
            self::Service => 'Service',
            self::Network => 'Network',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

}