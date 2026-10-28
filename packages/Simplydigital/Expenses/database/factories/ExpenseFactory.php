<?php

namespace Simplydigital\Expenses\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\Expenses\Enums\ExpenseType;
use Simplydigital\Expenses\Models\Expense;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $caseId = tickets::query()->value('id');

        if ($caseId === null) {
            throw new LogicException('Create a case before creating an expense.');
        }

        $date = now();

        return [
            'case_id' => $caseId,
            'user_id' => User::factory(),
            'week_number' => $date->isoWeek(),
            'week_year' => $date->isoWeekYear(),
            'task_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'type' => ExpenseType::Other,
            'description' => fake()->sentence(),
            'receipt_path' => null,
            'amount' => '25.00',
        ];
    }
}
