<?php

namespace Simplydigital\NotificationManagement\Policies;

use App\Models\User;
use Simplydigital\NotificationManagement\Enums\Permissions\NotificationPermission;
use Simplydigital\NotificationManagement\Models\Notification;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(NotificationPermission::View->value);
    }

    public function view(
        User $user,
        Notification $notification
    ): bool {
        return $user->can(NotificationPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(NotificationPermission::Create->value);
    }

    public function update(
        User $user
    ): bool {
        return $user->can(NotificationPermission::Update->value);
    }

    public function delete(
        User $user,
    ): bool {
        return $user->can(NotificationPermission::Delete->value);
    }
}
