<?php

namespace Simplydigital\Expenses\Enums;

enum ExpenseType: string
{
    case SiteVisit = 'site_visit';
    case OutsideOfficeHours = 'outside_office_hours';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SiteVisit => 'Site Visit',
            self::OutsideOfficeHours => 'Outside Office Hours',
            self::Other => 'Others',
        };
    }
}
