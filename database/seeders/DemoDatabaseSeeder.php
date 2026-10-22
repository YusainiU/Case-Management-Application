<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {

        if (app()->environment('production')) {
            $this->command?->error(
                'Demo seeders cannot run in production.'
            );

            return;
        }
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
