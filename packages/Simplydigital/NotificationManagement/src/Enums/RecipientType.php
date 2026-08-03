<?php

namespace Simplydigital\NotificationManagement\Enums;

enum RecipientType: string
{
    case To = 'to';

    case Cc = 'cc';

    case Bcc = 'bcc';

    case ReplyTo = 'reply_to';

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
