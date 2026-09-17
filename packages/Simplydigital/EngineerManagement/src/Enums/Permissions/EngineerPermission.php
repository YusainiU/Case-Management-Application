<?php

namespace Simplydigital\EngineerManagement\Enums\Permissions;

enum EngineerPermission: string
{
    case View = 'engineer.view';
    case Create = 'engineer.create';
    case Update = 'engineer.update';
    case Delete = 'engineer.delete';
}