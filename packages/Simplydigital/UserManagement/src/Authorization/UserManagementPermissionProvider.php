<?php

namespace Simplydigital\UserManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\UserManagement\Enums\Permissions\UserPermission;

class UserManagementPermissionProvider implements PermissionProvider
{

    public function permissions(): array
    {
        return [
            
            new PermissionDefinition(
                name: UserPermission::View->value,
                description: 'View User',
                group: 'Users',
            ),
            new PermissionDefinition(
                name: UserPermission::Update->value,
                description: 'Update User',
                group: 'Users',
            ),
            new PermissionDefinition(
                name: UserPermission::Create->value,
                description: 'Create User',
                group: 'Users',
            ),
            new PermissionDefinition(
                name: UserPermission::Delete->value,
                description: 'Delete User',
                group: 'Users',
            ),                        

        ];
    }

}