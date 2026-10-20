<?php

namespace Simplydigital\StockManagement\Authorization;

use Simplydigital\StockManagement\Enums\Permissions\StockRequestPermission;
use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class StockManagementPermissionProvider implements PermissionProvider
{
    public function permissions(): array
    {
        return [
            new PermissionDefinition(
                name: StockRequestPermission::Manage->value,
                description: 'Manage stock requests and update their progress',
                group: 'Stock Requests',
            ),
        ];
    }
}
