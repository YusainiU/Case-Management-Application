<?php

namespace Simplydigital\UserManagement\Services;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class PermissionRegistry
{
    /**
     * @var PermissionProvider[]
     */
    protected array $providers = [];

    public function registerProvider(PermissionProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @return PermissionDefinition[]
     */
    public function all(): array
    {
        $permissions = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->permissions() as $permission) {
                $permissions[$permission->name] = $permission;
            }
        }

        return array_values($permissions);
    }
}