<?php

namespace Simplydigital\NotificationManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EngineerManagement\Enums\Permissions\CertificatePermission;
class CertificatePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(CertificatePermission::View->value);
    }

    public function view(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CertificatePermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(CertificatePermission::Create->value);
    }

    public function update(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CertificatePermission::Update->value);
    }

    public function delete(
        User $user,
        tickets $case
    ): bool {
        return $user->can(CertificatePermission::Delete->value);
    }    

}