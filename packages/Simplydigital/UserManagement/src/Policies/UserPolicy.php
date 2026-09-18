<?php

namespace Simplydigital\UserManagement\Policies;

use App\Models\User;
use Simplydigital\UserManagement\Enums\Permissions\UserPermission;
class UserPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::View->value);
    }

    public function view(
        User $user
    ): bool {
        return $user->can(UserPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermission::Create->value);
    }

    public function update(
        User $user
    ): bool {
        return $user->can(UserPermission::Update->value);
    }

    public function delete(
        User $user
    ): bool {
        return $user->can(UserPermission::Delete->value);
    }    

}