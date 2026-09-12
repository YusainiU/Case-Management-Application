<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum SupplierPermission: string
{
    case View = 'supplier.view';
    case Create = 'supplier.create';
    case Update = 'supplier.update';
    case Delete = 'supplier.delete';
}
