<?php

namespace Simplydigital\Expenses\Authorization;

use Simplydigital\Expenses\Enums\Permissions\ExpensePermission;
use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class ExpensesPermissionProvider implements PermissionProvider
{
    /**
     * @return PermissionDefinition[]
     */
    public function permissions(): array
    {
        return [
            new PermissionDefinition(
                name: ExpensePermission::Create->value,
                description: 'Submit expense claims',
                group: 'Expenses',
            ),
            new PermissionDefinition(
                name: ExpensePermission::Update->value,
                description: 'Update pending or rejected expense claims',
                group: 'Expenses',
            ),
            new PermissionDefinition(
                name: ExpensePermission::Delete->value,
                description: 'Delete pending expense claims',
                group: 'Expenses',
            ),
            new PermissionDefinition(
                name: ExpensePermission::View->value,
                description: 'View personal expense claims',
                group: 'Expenses',
            ),
            new PermissionDefinition(
                name: ExpensePermission::Admin->value,
                description: 'Review and report on all expense claims',
                group: 'Expenses',
            ),
        ];
    }
}
