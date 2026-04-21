<?php

namespace Database\Seeders;

use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        Product::factory(20)->create();
        
    }
}
