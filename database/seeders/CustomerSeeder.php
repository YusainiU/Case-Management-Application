<?php

namespace Database\Seeders;

use Simplydigital\CaseManagement\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Customer::factory()
            ->count(30)
            ->create();
    }
}