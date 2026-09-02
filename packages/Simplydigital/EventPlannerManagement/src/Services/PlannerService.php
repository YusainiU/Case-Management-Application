<?php

namespace Simplydigital\EventPlannerManagement\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;

class PlannerService
{
    public function __construct(
        protected PlannerEventProviderRegistry $registry,
    ) {}

    public function registerProvider(
        PlannerEventProvider $provider
    ): void {
        $this->registry->register($provider);
    }    

    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection {
        return collect($this->registry->providers())
            ->flatMap(
                fn ($provider) => $provider->events(
                    $start,
                    $end,
                    $userId
                )
            )
            ->sortBy('start')
            ->values();
    }
}
