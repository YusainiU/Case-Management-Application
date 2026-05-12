<?php

namespace Simplydigital\CaseManagement\Enums;

enum CaseSource: string
{
    case Phone = 'Phone';
    case Email = 'Email';
    case Portal = 'Portal';

    public function label(): string
    {
        return match($this) {
            self::Phone => 'Phone',
            self::Email => 'Email',
            self::Portal => 'Portal',
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }       

}