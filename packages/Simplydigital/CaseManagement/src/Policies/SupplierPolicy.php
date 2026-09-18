<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\SupplierPermission;
class SupplierPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(SupplierPermission::View->value);
    }

    public function view(
        User $user
    ): bool {
        return $user->can(SupplierPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SupplierPermission::Create->value);
    }

    public function update(
        User $user
    ): bool {
        return $user->can(SupplierPermission::Update->value);
    }

    public function delete(
        User $user
    ): bool {
        return $user->can(SupplierPermission::Delete->value);
    }    

}