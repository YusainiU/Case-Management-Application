<?php

namespace Simplydigital\EventPlannerManagement\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\EventPlannerManagement\Support\DateRange;

interface PlannerEventProvider
{

    /**
     * Return planner events for the supplied date range.
     */
    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection;

    /**
     * @return iterable<int, PlannerEvent>
     */
    public function getEvents(?DateRange $range = null): iterable;

    public function getEventById(string $id): ?PlannerEvent;
}
