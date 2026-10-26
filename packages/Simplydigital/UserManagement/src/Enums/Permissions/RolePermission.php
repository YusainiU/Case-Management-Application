<?php

namespace Simplydigital\UserManagement\Enums\Permissions;

enum RolePermission: string
{
    case View = 'Role.view';
    case Create = 'Role.create';
    case Update = 'Role.update';
    case Delete = 'Role.delete';

}