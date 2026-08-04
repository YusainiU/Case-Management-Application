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
    
    case EmailLogNotification='emails.log-notification';

    case EmailUrgentNotification='emails.urgent-notification';

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

    /**
     * Get the view path for this template
     */
    public function view(): string
    {
        return "notification::{$this->value}";
    }    
    
    /**
     * Check if template exists
     */
    public function exists(): bool
    {
        return view()->exists($this->view());
    }    

}