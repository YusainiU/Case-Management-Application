<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum CustomerContactPermission: string
{
    case View = 'customer_contact.view';
    case Create = 'customer_contact.create';
    case Update = 'customer_contact.update';
    case Delete = 'customer_contact.delete';
}
