<?php

namespace Simplydigital\EngineerManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EngineerManagement\Enums\Permissions\EngineerPermission;
use Simplydigital\EngineerManagement\Models\EngineerManagement;
class EngineerPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(EngineerPermission::View->value);
    }

    public function view(
        User $user,
        EngineerManagement $engineerManagement
    ): bool {
        return $user->can(EngineerPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(EngineerPermission::Create->value);
    }

    public function update(
        User $user,
        EngineerManagement $engineerManagement
    ): bool {
        return $user->can(EngineerPermission::Update->value);
    }

    public function delete(
        User $user,
        EngineerManagement $engineerManagement
    ): bool {
        return $user->can(EngineerPermission::Delete->value);
    }    

}