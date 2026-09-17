<?php

namespace Simplydigital\NotificationManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\NotificationManagement\Enums\Permissions\NotificationPermission;

class NotificationManagementPermissionProvider implements PermissionProvider
{

    public function permissions(): array
    {
        return [
            
            new PermissionDefinition(
                name: NotificationPermission::View->value,
                description: 'View notification',
                group: 'Notifications',
            ),
            new PermissionDefinition(
                name: NotificationPermission::Update->value,
                description: 'Update notification',
                group: 'Notifications',
            ),
            new PermissionDefinition(
                name: NotificationPermission::Create->value,
                description: 'Create notification',
                group: 'Notifications',
            ),
            new PermissionDefinition(
                name: NotificationPermission::Delete->value,
                description: 'Delete notification',
                group: 'Notifications',
            ),                        

        ];
    }

}