<?php

namespace Simplydigital\EngineerManagement\Enums\Permissions;

enum SkillPermission: string
{
    case View = 'skill.view';
    case Create = 'skill.create';
    case Update = 'skill.update';
    case Delete = 'skill.delete';
}