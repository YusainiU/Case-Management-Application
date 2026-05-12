<?php

namespace Simplydigital\CaseManagement\Enums;

enum CaseCategory: string
{
    case Failure = 'Failure';
    case PartReplacement = 'Part Replacement';
    case TechnicalSupport = 'Technical Support';

    public function label(): string
    {
        return match($this) {
            self::Failure => 'Failure',
            self::PartReplacement => 'Part Replacement',
            self::TechnicalSupport => 'Technical Support',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }    

}