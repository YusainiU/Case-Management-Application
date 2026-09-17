<?php

namespace Simplydigital\EventPlannerManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\EventPlannerManagement\Enums\Permissions\EventPermission;

class EventManagementPermissionProvider implements PermissionProvider
{

    public function permissions(): array
    {
        return [
            
            new PermissionDefinition(
                name: EventPermission::View->value,
                description: 'View Events',
                group: 'Events',
            ),
            new PermissionDefinition(
                name: EventPermission::Update->value,
                description: 'Update Events',
                group: 'Events',
            ),
            new PermissionDefinition(
                name: EventPermission::Create->value,
                description: 'Create Events',
                group: 'Events',
            ),
            new PermissionDefinition(
                name: EventPermission::Delete->value,
                description: 'Delete Events',
                group: 'Events',
            ),                        

        ];
    }

}