<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum ProductPermission: string
{
    case View = 'product.view';
    case Create = 'product.create';
    case Update = 'product.update';
    case Delete = 'product.delete';
}
