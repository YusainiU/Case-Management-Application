<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie's cached permissions and roles.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'Super Admin',
            'Admin',
            'Internal User',
            'Customer',
            'Engineer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // Clear the cache again to ensure fresh data.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
