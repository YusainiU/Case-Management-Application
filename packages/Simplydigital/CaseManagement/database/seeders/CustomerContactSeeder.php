<?php

namespace Simplydigital\CaseManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Simplydigital\CaseManagement\Models\CustomerContacts;

class CustomerContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CustomerContacts::factory()
            ->count(5)
            ->create();
    }
}
