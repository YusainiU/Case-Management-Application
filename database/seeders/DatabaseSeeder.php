<?php

namespace Database\Seeders;

use Simplydigital\CaseManagement\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        Customer::factory(20)->create();
        
    }
}
