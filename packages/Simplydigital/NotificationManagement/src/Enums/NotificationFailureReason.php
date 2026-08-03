<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationFailureReason : string
{
    case None='none';

    case SMTP='smtp';

    case Authentication='authentication';

    case InvalidRecipient='invalid_recipient';

    case Timeout='timeout';

    case RateLimited='rate_limited';

    case Unknown='unknown';

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