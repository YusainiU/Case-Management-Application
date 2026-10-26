<?php

namespace Simplydigital\UserManagement\Policies;

use App\Models\User;
use Simplydigital\UserManagement\Enums\Permissions\RolePermission;

class RolePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(RolePermission::View->value);
    }

    public function view(
        User $user,
        RolePermission $rolePermission
    ): bool {
        return $user->can(RolePermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(RolePermission::Create->value);
    }

    public function update(
        User $user,
        RolePermission $rolePermission
    ): bool {
        return $user->can(RolePermission::Update->value);
    }

    public function delete(
        User $user,
        RolePermission $rolePermission
    ): bool {
        return $user->can(RolePermission::Delete->value);
    }    

}