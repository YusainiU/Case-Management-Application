<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationChannel: string
{
    case Email = 'email';

    case Sms = 'sms';

    case Push = 'push';

    case Teams = 'teams';

    case Slack = 'slack';

    case WhatsApp = 'whatsapp';

    case Webhook = 'webhook';

    case Internal = 'internal';


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
