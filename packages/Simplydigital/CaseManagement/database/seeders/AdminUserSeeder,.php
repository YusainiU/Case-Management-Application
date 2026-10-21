<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@simplydigital.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make(
                    'selangor'
                ),
            ]
        );

        $user->assignRole($role);
    }
}
