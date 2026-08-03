<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationStatus: string
{
    case Draft = 'draft';

    case Pending = 'pending';

    case Queued = 'queued';

    case Sending = 'sending';

    case Sent = 'sent';

    case Delivered = 'delivered';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

    case Expired = 'expired';

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

    public function isFinished(): bool
    {
        return match ($this) {

            self::Sent,
            self::Delivered,
            self::Failed,
            self::Cancelled,
            self::Expired => true,

            default => false,

        };
    }

    public function colour(): string
    {
        return match ($this) {

            self::Sent => 'success',
            self::Failed => 'danger',
            self::Queued => 'warning',
            self::Sending => 'info',
            default => 'secondary',
        };
    }
}
