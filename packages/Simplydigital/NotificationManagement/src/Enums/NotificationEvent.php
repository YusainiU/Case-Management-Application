<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationEvent : string
{
    case Created='created';

    case Queued='queued';

    case Sending='sending';

    case Sent='sent';

    case Delivered='delivered';

    case Failed='failed';

    case Retried='retried';

    case Cancelled='cancelled';

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