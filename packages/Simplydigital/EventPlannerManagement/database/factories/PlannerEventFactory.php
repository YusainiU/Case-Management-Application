<?php

namespace Simplydigital\EventPlannerManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Simplydigital\EventPlannerManagement\Models\PlannerEvent;

class PlannerEventFactory extends Factory
{
    protected $model = PlannerEvent::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+2 weeks');
        $end = (clone $start)->modify('+1 hour');

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['meeting', 'conference', 'workshop', 'deadline', 'reminder']),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'completed']),
            'starts_at' => $start,
            'ends_at' => $end,
            'location' => $this->faker->optional()->city(),
            'metadata' => [],
        ];
    }
}
