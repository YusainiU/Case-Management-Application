<?php

namespace Simplydigital\UserManagement\Services;

use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class PermissionRegistry
{
    protected array $permissions = [];

    public function registerProvider(
        PermissionProvider $provider
    ): void {
        $this->register(...$provider->permissions());
    }

    public function register(
        PermissionDefinition ...$permissions
    ): void {
        foreach ($permissions as $permission) {
            $this->permissions[$permission->name] = $permission;
        }
    }

    public function all(): array
    {
        return array_values($this->permissions);
    }

    public function find(string $name): ?PermissionDefinition
    {
        return $this->permissions[$name] ?? null;
    }
}