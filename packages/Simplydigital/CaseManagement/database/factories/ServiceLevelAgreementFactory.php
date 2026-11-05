<?php

namespace Simplydigital\CaseManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Enums\DaysOfWeek;
use Simplydigital\CaseManagement\Enums\slaTypes;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;

/**
 * @extends Factory<serviceLevelAgreements>
 */
class ServiceLevelAgreementFactory extends Factory
{
    protected $model = serviceLevelAgreements::class;

    public function definition(): array
    {
        $faker = fake('en_GB');

        return [
            'name' => $faker->words(3, true),
            'short_description' => $faker->randomElement([
                'Standard Cover',
                'Premium Cover',
                'Gold Cover',
                'Silver Cover',
                'Hardware Replacement Only',
            ]),
            'start_day' => $faker->randomElement(DaysOfWeek::cases())->value,
            'end_day' => $faker->randomElement(DaysOfWeek::cases())->value,
            'service_start_time' => $faker->numberBetween(8, 10) * 3600,
            'service_end_time' => $faker->numberBetween(17, 19) * 3600,
            'include_public_holiday' => $faker->boolean(),
            'cover_details' => $faker->sentence(),
            'active' => $faker->boolean(90),
            'type' => $faker->randomElement(slaTypes::cases())->value,
            'response_time' => $faker->numberBetween(0, 99),
            'fixed_time' => $faker->numberBetween(0, 99),
        ];
    }
}
