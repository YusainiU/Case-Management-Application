<?php

namespace Simplydigital\CaseManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;

class ServiceLevelAgreementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        serviceLevelAgreements::factory()
            ->count(5)
            ->create();
    }
}
