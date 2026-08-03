<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationTrigger : string
{
    case ServiceLog='service_log';

    case Invoice='invoice';

    case Quote='quote';

    case Contract='contract';

    case Customer='customer';

    case User='user';

    public function label(): string
    {
        return str($this->name)
            ->headline()
            ->toString();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->label(),
            ])
            ->all();
    } 
        
}