<?php

namespace Simplydigital\CaseManagement\Policies;

use Simplydigital\CaseManagement\Models\Customer;
use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerPermission;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(CustomerPermission::View->value);
    }

    public function view(
        User $user,
        Customer $customer
    ): bool {
        return $user->can(CustomerPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(CustomerPermission::Create->value);
    }

    public function update(
        User $user,
        Customer $customer
    ): bool {
        return $user->can(CustomerPermission::Update->value);
    }

    public function delete(
        User $user,
        Customer $customer
    ): bool {
        return $user->can(CustomerPermission::Delete->value);
    }
}