<?php

namespace Simplydigital\Expenses\Enums\Permissions;

enum ExpensePermission: string
{
    case Create = 'expense.create';
    case Update = 'expense.update';
    case Delete = 'expense.delete';
    case View = 'expense.view';
    case Admin = 'expense.admin';
}
