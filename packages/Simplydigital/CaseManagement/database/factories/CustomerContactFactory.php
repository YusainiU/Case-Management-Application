<?php

namespace Simplydigital\CaseManagement\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;

/**
 * @extends Factory<CustomerContacts>
 */
class CustomerContactFactory extends Factory
{
    protected $model = CustomerContacts::class;

    public function configure(): static
    {
        return $this->afterCreating(function (CustomerContacts $customerContact): void {
            User::query()->findOrFail($customerContact->user_id)->assignRole('Customer');
        });
    }

    public function definition(): array
    {
        $faker = fake('en_GB');

        return [
            'user_id' => User::query()
                ->doesntHave('roles')
                ->inRandomOrder()
                ->firstOrFail()
                ->getKey(),
            'customer_id' => Customer::query()->inRandomOrder()->firstOrFail()->getKey(),
            'customerRole' => $faker->randomElement(CustomerContactRoles::cases())->value,
            'description' => $faker->sentence(),
            'active' => $faker->boolean(90),
            'has_portal_access' => true,
        ];
    }
}
