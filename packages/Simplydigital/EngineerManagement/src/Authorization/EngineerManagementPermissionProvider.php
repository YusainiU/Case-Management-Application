<?php

namespace Simplydigital\EngineerManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\EngineerManagement\Enums\Permissions\EngineerPermission;
use Simplydigital\EngineerManagement\Enums\Permissions\CertificatePermission;
use Simplydigital\EngineerManagement\Enums\Permissions\SkillPermission;

class EngineerManagementPermissionProvider implements PermissionProvider
{

    public function permissions(): array
    {
        return [
            
            new PermissionDefinition(
                name: EngineerPermission::View->value,
                description: 'View Engineer',
                group: 'Engineers',
            ),
            new PermissionDefinition(
                name: EngineerPermission::Update->value,
                description: 'Update Engineer',
                group: 'Engineers',
            ),
            new PermissionDefinition(
                name: EngineerPermission::Create->value,
                description: 'Create Engineer',
                group: 'Engineers',
            ),
            new PermissionDefinition(
                name: EngineerPermission::Delete->value,
                description: 'Delete Engineer',
                group: 'Engineers',
            ),
            new PermissionDefinition(
                name: CertificatePermission::View->value,
                description: 'View Certificate',
                group: 'Certificates',
            ),
            new PermissionDefinition(
                name: CertificatePermission::Update->value,
                description: 'Update Certificate',
                group: 'Certificates',
            ),
            new PermissionDefinition(
                name: CertificatePermission::Create->value,
                description: 'Create Certificates',
                group: 'Certificates',
            ),
            new PermissionDefinition(
                name: CertificatePermission::Delete->value,
                description: 'Delete Certificate',
                group: 'Certificate',
            ),
            new PermissionDefinition(
                name: SkillPermission::View->value,
                description: 'View Skill',
                group: 'Skills',
            ),
            new PermissionDefinition(
                name: SkillPermission::Update->value,
                description: 'Update Skill',
                group: 'Skills',
            ),
            new PermissionDefinition(
                name: SkillPermission::Create->value,
                description: 'Create Skill',
                group: 'Skills',
            ),
            new PermissionDefinition(
                name: SkillPermission::Delete->value,
                description: 'Delete Skill',
                group: 'Skills',
            ),                        
                                    

        ];
    }

}