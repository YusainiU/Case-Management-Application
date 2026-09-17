<?php

namespace Simplydigital\EventPlannerManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EventPlannerManagement\Enums\Permissions\EventPermission;
class EventPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(EventPermission::View->value);
    }

    public function view(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EventPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(EventPermission::Create->value);
    }

    public function update(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EventPermission::Update->value);
    }

    public function delete(
        User $user,
        tickets $case
    ): bool {
        return $user->can(EventPermission::Delete->value);
    }    

}