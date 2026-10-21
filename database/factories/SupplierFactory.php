<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Models\Supplier;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $faker = fake('en_GB');

        return [
            'name' => $faker->company(),
            'address_1' => $faker->buildingNumber() . ' ' . $faker->streetName(),
            'address_2' => $faker->optional()->secondaryAddress(),
            'address_3' => $faker->city(),
            'address_4' => $faker->county(),
            'telephone' => $faker->phoneNumber(),
            'email' => $faker->unique()->companyEmail(),
            'url' => $faker->optional()->url(),
            'active' => $faker->boolean(90),
            'short_description' => $faker->sentence(10),
        ];
    }
}
