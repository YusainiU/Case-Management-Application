<?php

namespace Simplydigital\NotificationManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EngineerManagement\Enums\Permissions\EngineerPermission;
class EngineerPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(EngineerPermission::View->value);
    }

    public function view(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EngineerPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(EngineerPermission::Create->value);
    }

    public function update(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EngineerPermission::Update->value);
    }

    public function delete(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EngineerPermission::Delete->value);
    }    

}