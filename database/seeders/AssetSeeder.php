<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Simplydigital\CaseManagement\Models\Asset;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Asset::factory()
            ->count(10)
            ->create();
    }
}
