<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationResult : string
{
    case Success='success';

    case TemporaryFailure='temporary_failure';

    case PermanentFailure='permanent_failure';

    case Timeout='timeout';

    case Rejected='rejected';

    case Bounced='bounced';

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