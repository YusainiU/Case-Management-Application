<?php

namespace Simplydigital\NotificationManagement\Enums\Permissions;

enum NotificationPermission: string
{
    case View = 'notification.view';
    case Create = 'notification.create';
    case Update = 'notification.update';
    case Delete = 'notification.delete';

}
