<?php

namespace Simplydigital\UserManagement\Services;

use Spatie\Permission\Models\Permission;

class PermissionSynchronizer
{
    public function __construct(
        protected PermissionRegistry $registry,
    ) {
    }

    public function sync(): void
    {
        foreach ($this->registry->all() as $definition) {

            $permission = Permission::findOrCreate(
                $definition->name,
                $definition->guard
            );

            // $permission->description = $definition->description;
            $permission->save();                 
        }
   
    }
}