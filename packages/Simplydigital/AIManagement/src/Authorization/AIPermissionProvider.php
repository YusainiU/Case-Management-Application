<?php

namespace Simplydigital\AIManagement\Authorization;

use Simplydigital\AIManagement\Authorization\Permissions\AIPermission;
use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class AIPermissionProvider implements PermissionProvider
{
    public function permissions(): array
    {
        return [
            new PermissionDefinition(
                name: AIPermission::CaseAssistantView->value,
                description: 'View AI case assistant',
                group: 'AI Case Assistant',
            ),

            new PermissionDefinition(
                name: AIPermission::CaseAssistantAnalyze->value,
                description: 'Run AI case analysis',
                group: 'AI Case Assistant',
            ),

            new PermissionDefinition(
                name: AIPermission::CaseAssistantApply->value,
                description: 'Apply AI case assistant suggestions',
                group: 'AI Case Assistant',
            ),
        ];
    }
}
