<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerContactPermission;

class CustomerContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(CustomerContactPermission::View->value);
    }

    public function view(
        User $user,
        CustomerContacts $customerContact
    ): bool {
        return $user->can(CustomerContactPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(CustomerContactPermission::Create->value);
    }

    public function update(
        User $user,
        CustomerContacts $customerContact
    ): bool {
        return $user->can(CustomerContactPermission::Update->value);
    }

    public function delete(
        User $user,
        CustomerContacts $customerContact
    ): bool {
        return $user->can(CustomerContactPermission::Delete->value);
    }
}