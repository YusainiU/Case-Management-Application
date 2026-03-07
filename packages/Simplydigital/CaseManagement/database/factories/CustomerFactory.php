<?php

namespace Simplydigital\CaseManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Simplydigital\CaseManagement\Models\Customer;

class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'short_description' => fake()->text(),
            'primary_type' => CustomerPrimaryTypes::Main_Company,
            'url' => fake()->url(),
            'address_1' => fake()->address(),
            'address_2' => fake()->streetName(),
            'address_3' => fake()->postcode(),
            'telephone_1' => fake()->phoneNumber(),
        ];
    }
}