<?php

Namespace Simplydigital\CaseManagement\Enums;

enum CustomerPrimaryTypes: string
{
    case Main_Company = "main_company";
    case Child_Company = "child_company";

    public function label(): string
    {
        return match($this) {
            self::Main_Company => 'Main Company',
            self::Child_Company => 'Child Company',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

}