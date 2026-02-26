<?php

namespace Simplydigital\CaseManagement\Enums;

enum CustomerChildTypes: string
{
    case Warehouse = 'warehouse';
    case Branch = 'branch';
    case Shop = 'shop';
    case Outlet = 'outlet';

    public function label(): string
    {
        return match($this) {
            self::Warehouse => 'Warehouse',
            self::Branch => 'Branch',
            self::Shop => 'Shop',
            self::Outlet => 'Outlet',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

}
