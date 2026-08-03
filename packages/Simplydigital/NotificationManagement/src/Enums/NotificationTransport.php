<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationTransport: string
{
    case SMTP = 'smtp';

    case SES = 'ses';

    case Mailgun = 'mailgun';

    case Postmark = 'postmark';

    case Sendgrid = 'sendgrid';

    case Local = 'local';

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
