<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Enums\Permissions\CasePermission;

class CasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(CasePermission::View->value);
    }

    public function view(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CasePermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(CasePermission::Create->value);
    }

    public function update(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CasePermission::Update->value);
    }

    public function delete(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CasePermission::Delete->value);
    }
}