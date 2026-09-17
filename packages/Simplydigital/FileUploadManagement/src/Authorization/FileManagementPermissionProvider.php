<?php

namespace Simplydigital\FileUploadManagement\Authorization;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;
use Simplydigital\FileUploadManagement\Enums\Permissions\FilePermission;

class FileManagementPermissionProvider implements PermissionProvider
{

    public function permissions(): array
    {
        return [
            
            new PermissionDefinition(
                name: FilePermission::View->value,
                description: 'View Files',
                group: 'Files',
            ),
            new PermissionDefinition(
                name: FilePermission::Update->value,
                description: 'Update Files',
                group: 'Files',
            ),
            new PermissionDefinition(
                name: FilePermission::Create->value,
                description: 'Create Files',
                group: 'Files',
            ),
            new PermissionDefinition(
                name: FilePermission::Delete->value,
                description: 'Delete File',
                group: 'Files',
            ),                        

        ];
    }

}