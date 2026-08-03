<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationTemplate : string
{
    case UrgentLog='emails.logs.urgent';

    case Invoice='emails.invoice';

    case Quote='emails.quote';

    case PasswordReset='emails.password';

    case Welcome='emails.welcome';

    case EmailNotification='emails.notification';

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