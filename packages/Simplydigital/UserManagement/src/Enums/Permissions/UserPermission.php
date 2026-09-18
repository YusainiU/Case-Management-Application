<?php

namespace Simplydigital\UserManagement\Enums\Permissions;

enum UserPermission: string
{
    case View = 'User.view';
    case Create = 'User.create';
    case Update = 'User.update';
    case Delete = 'User.delete';

}
