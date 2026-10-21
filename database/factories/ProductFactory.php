<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Models\Product;
use Simplydigital\CaseManagement\Enums\ProductTypes;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_code' => fake()->randomDigit(),
            'name' => strtoupper(fake()->text(10)),
            'short_description' => fake()->text(50),
            'model' => strtoupper(fake()->text(10)),
            'type' => ProductTypes::Network->value,
            'make' => 'British Telecoms',
            'version' => fake()->randomDigit(),
            'active' => true,
        ];
    }
}