<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum CustomerPermission: string
{
    case View = 'customer.view';
    case Create = 'customer.create';
    case Update = 'customer.update';
    case Delete = 'customer.delete';
}
