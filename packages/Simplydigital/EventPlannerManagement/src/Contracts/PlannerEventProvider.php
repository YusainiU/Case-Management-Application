<?php

namespace Simplydigital\EventPlannerManagement\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

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

}
