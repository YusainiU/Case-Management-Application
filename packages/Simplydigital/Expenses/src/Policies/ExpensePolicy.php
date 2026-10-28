<?php

namespace Simplydigital\Expenses\Policies;

use App\Models\User;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Enums\Permissions\ExpensePermission;
use Simplydigital\Expenses\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ExpensePermission::View->value)
            || $user->can(ExpensePermission::Admin->value);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can(ExpensePermission::Admin->value)
            || ($user->can(ExpensePermission::View->value)
                && $expense->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can(ExpensePermission::Create->value);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can(ExpensePermission::Update->value)
            && $expense->user_id === $user->id
            && in_array($expense->status, [
                ExpenseStatus::AwaitingApproval,
                ExpenseStatus::Rejected,
            ], true);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can(ExpensePermission::Delete->value)
            && $expense->user_id === $user->id
            && $expense->status === ExpenseStatus::AwaitingApproval;
    }

    public function manageAny(User $user): bool
    {
        return $user->can(ExpensePermission::Admin->value);
    }

    public function review(User $user, Expense $expense): bool
    {
        return $user->can(ExpensePermission::Admin->value)
            && $expense->user_id !== $user->id
            && $expense->status->canBeReviewed();
    }
}
