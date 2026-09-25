<?php

namespace Simplydigital\AIManagement\Policies;

use App\Models\User;
use Simplydigital\AIManagement\Authorization\Permissions\AIPermission;

class CaseAssistantPolicy
{
    public function view(
        User $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantView->value
        );
    }

    public function analyze(
        User $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantAnalyze->value
        );
    }

    public function apply(
        User $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantApply->value
        );
    }
}
