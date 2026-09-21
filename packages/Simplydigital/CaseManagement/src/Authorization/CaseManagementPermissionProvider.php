<?php

namespace Simplydigital\CaseManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerPermission;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerContactPermission;
use Simplydigital\CaseManagement\Enums\Permissions\CasePermission;
use Simplydigital\CaseManagement\Enums\Permissions\ServiceLevelAgreementPermission as sla;
use Simplydigital\CaseManagement\Enums\Permissions\ProductPermission;
use Simplydigital\CaseManagement\Enums\Permissions\SupplierPermission;
use Simplydigital\CaseManagement\Enums\Permissions\AssetPermission;
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

            //SLA - Service Level Agreement
            new PermissionDefinition(
                name: sla::View->value,
                description: 'View Service Level Agreement',
                group: 'Service Level Agreement',
            ),

            new PermissionDefinition(
                name: sla::Create->value,
                description: 'Create Service Level Agreement',
                group: 'Service Level Agreement',
            ),

            new PermissionDefinition(
                name: sla::Update->value,
                description: 'Update Service Level Agreement',
                group: 'Service Level Agreement',
            ),
            
            new PermissionDefinition(
                name: sla::Delete->value,
                description: 'Delete Service Level Agreement',
                group: 'Service Level Agreement',
            ),

            //Products
            new PermissionDefinition(
                name: ProductPermission::View->value,
                description: 'View Products',
                group: 'Products',
            ),

            new PermissionDefinition(
                name: ProductPermission::Create->value,
                description: 'Create Products',
                group: 'Products',
            ),

            new PermissionDefinition(
                name: ProductPermission::Update->value,
                description: 'Update Products',
                group: 'Products',
            ),
            
            new PermissionDefinition(
                name: ProductPermission::Delete->value,
                description: 'Delete Products',
                group: 'Products',
            ),                     

            //Supplier
            new PermissionDefinition(
                name: SupplierPermission::View->value,
                description: 'View Suppliers',
                group: 'Suppliers',
            ),

            new PermissionDefinition(
                name: SupplierPermission::Create->value,
                description: 'Create Suppliers',
                group: 'Suppliers',
            ),

            new PermissionDefinition(
                name: SupplierPermission::Update->value,
                description: 'Update Suppliers',
                group: 'Suppliers',
            ),
            
            new PermissionDefinition(
                name: SupplierPermission::Delete->value,
                description: 'Delete Suppliers',
                group: 'Suppliers',
            ),

            //Asset
            new PermissionDefinition(
                name: AssetPermission::View->value,
                description: 'View Assets',
                group: 'Assets',
            ),

            new PermissionDefinition(
                name: AssetPermission::Create->value,
                description: 'Create Assets',
                group: 'Assets',
            ),

            new PermissionDefinition(
                name: AssetPermission::Update->value,
                description: 'Update Assets',
                group: 'Assets',
            ),
            
            new PermissionDefinition(
                name: AssetPermission::Delete->value,
                description: 'Delete Assets',
                group: 'Assets',
            ),            


        ];
    }
}