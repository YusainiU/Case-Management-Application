<?php

namespace Simplydigital\CaseManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Models\Asset;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\Product;
use Simplydigital\CaseManagement\Models\Supplier;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $faker = fake('en_GB');

        return [
            'short_description' => $faker->sentence(),
            'customer_id' => Customer::query()->inRandomOrder()->firstOrFail()->getKey(),
            'product_id' => Product::query()->inRandomOrder()->firstOrFail()->getKey(),
            'supplier_id' => Supplier::query()->inRandomOrder()->firstOrFail()->getKey(),
            'buy_price' => $faker->randomFloat(2, 0, 10000),
            'sell_price' => $faker->randomFloat(2, 0, 10000),
            'license_number' => $faker->bothify('??##??##'),
            'active' => $faker->boolean(90),
            'notes' => $faker->optional()->sentence(),
            'location' => $faker->city(),
            'technical_specifications' => $faker->sentence(),
            'mac_address' => $faker->macAddress(),
            'ip_address' => $faker->ipv4(),
        ];
    }
}
