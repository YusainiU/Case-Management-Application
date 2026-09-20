<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\AssetPermission;
use Simplydigital\CaseManagement\Models\Asset;
class AssetPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(AssetPermission::View->value);
    }

    public function view(
        User $user
    ): bool {
        return $user->can(AssetPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(AssetPermission::Create->value);
    }

    public function update(
        User $user,
        Asset $asset 
    ): bool {
        return $user->can(AssetPermission::Update->value);
    }

    public function delete(
        User $user,
        Asset $asset
    ): bool {
        return $user->can(AssetPermission::Delete->value);
    }    

}