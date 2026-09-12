<?php

namespace Simplydigital\UserManagement\Data;

class PermissionDefinition
{ 
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $group,
        public readonly string $guard = 'web',
    ) {}
    
}