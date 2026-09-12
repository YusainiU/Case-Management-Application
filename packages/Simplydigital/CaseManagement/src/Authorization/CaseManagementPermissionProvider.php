<?php

namespace Simplydigital\CaseManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerPermission;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerContactPermission;
use Simplydigital\CaseManagement\Enums\Permissions\CasePermission;
class CaseManagementPermissionProvider implements PermissionProvider
{
    public function permissions(): array
    {
        return [

            // Customers
            new PermissionDefinition(
                name: CustomerPermission::View->value,
                description: 'View customer details',
                group: 'Customers',
            ),

            new PermissionDefinition(
                name: CustomerPermission::Create->value,
                description: 'Create customers',
                group: 'Customers',
            ),

            new PermissionDefinition(
                name: CustomerPermission::Update->value,
                description: 'Update customer details',
                group: 'Customers',
            ),

            new PermissionDefinition(
                name: CustomerPermission::Delete->value,
                description: 'Delete customers',
                group: 'Customers',
            ),

            // Customer Contacts
            new PermissionDefinition(
                name: CustomerContactPermission::View->value,
                description: 'View customer contacts',
                group: 'Customer Contacts',
            ),

            new PermissionDefinition(
                name: CustomerContactPermission::Create->value,
                description: 'Create customer contacts',
                group: 'Customer Contacts',
            ),

            new PermissionDefinition(
                name: CustomerContactPermission::Update->value,
                description: 'Update customer contacts',
                group: 'Customer Contacts',
            ),

            new PermissionDefinition(
                name: CustomerContactPermission::Delete->value,
                description: 'Delete customer contacts',
                group: 'Customer Contacts',
            ),

            // Cases
            new PermissionDefinition(
                name: CasePermission::View->value,
                description: 'View cases',
                group: 'Cases',
            ),

            new PermissionDefinition(
                name: CasePermission::Create->value,
                description: 'Create cases',
                group: 'Cases',
            ),

            new PermissionDefinition(
                name: CasePermission::Update->value,
                description: 'Update cases',
                group: 'Cases',
            ),

            new PermissionDefinition(
                name: CasePermission::Delete->value,
                description: 'Delete cases',
                group: 'Cases',
            ),
        ];
    }
}