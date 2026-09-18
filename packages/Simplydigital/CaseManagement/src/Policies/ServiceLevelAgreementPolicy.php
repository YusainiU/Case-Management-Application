<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\ServiceLevelAgreementPermission;
class ServiceLevelAgreementPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(ServiceLevelAgreementPermission::View->value);
    }

    public function view(
        User $user
    ): bool {
        return $user->can(ServiceLevelAgreementPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(ServiceLevelAgreementPermission::Create->value);
    }

    public function update(
        User $user
    ): bool {
        return $user->can(ServiceLevelAgreementPermission::Update->value);
    }

    public function delete(
        User $user
    ): bool {
        return $user->can(ServiceLevelAgreementPermission::Delete->value);
    }    

}