<?php

namespace Simplydigital\FileUploadManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\FileUploadManagement\Enums\Permissions\FilePermission;
class FilePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(FilePermission::View->value);
    }

    public function view(
        User $user,
        tickets $case
    ): bool {
        return $user->can(FilePermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(FilePermission::Create->value);
    }

    public function update(
        User $user,
        tickets $case
    ): bool {
        return $user->can(FilePermission::Update->value);
    }

    public function delete(
        User $user,
        tickets $case
    ): bool {
        return $user->can(FilePermission::Delete->value);
    }    

}