<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\ProductPermission;
class ProductPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(ProductPermission::View->value);
    }

    public function view(
        User $user
    ): bool {
        return $user->can(ProductPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(ProductPermission::Create->value);
    }

    public function update(
        User $user
    ): bool {
        return $user->can(ProductPermission::Update->value);
    }

    public function delete(
        User $user
    ): bool {
        return $user->can(ProductPermission::Delete->value);
    }    

}