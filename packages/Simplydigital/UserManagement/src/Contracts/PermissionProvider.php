<?php

namespace Simplydigital\UserManagement\Contracts;

use Simplydigital\UserManagement\Data\PermissionDefinition;

interface PermissionProvider
{
    /**
     * @return PermissionDefinition[]
     */
    public function permissions(): array;
}