<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum CustomerContactPermission: string
{
    case View = 'customer_contacts.view';
    case Create = 'customer_contacts.create';
    case Update = 'customer_contacts.update';
    case Delete = 'customer_contacts.delete';
}
