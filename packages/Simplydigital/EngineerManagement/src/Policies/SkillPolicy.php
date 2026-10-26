<?php

namespace Simplydigital\EngineerManagement\Policies;

use App\Models\User;
use Laravel\Boost\Install\Skill;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EngineerManagement\Enums\Permissions\SkillPermission;
class SkillPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(SkillPermission::View->value);
    }

    public function view(
        User $user,
        Skill $skill
    ): bool {
        return $user->can(SkillPermission::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SkillPermission::Create->value);
    }

    public function update(
        User $user,
        Skill $skill
    ): bool {
        return $user->can(SkillPermission::Update->value);
    }

    public function delete(
        User $user,
        Skill $skill
    ): bool {
        return $user->can(SkillPermission::Delete->value);
    }    

}