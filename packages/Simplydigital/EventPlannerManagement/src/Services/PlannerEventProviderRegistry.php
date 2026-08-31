<?php

namespace Simplydigital\EventPlannerManagement\Services;

use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;

class PlannerEventProviderRegistry
{
    /**
     * @var array<string, PlannerEventProvider>
     */
    protected array $providers = [];

    public function register(PlannerEventProvider $provider): void
    {
        $this->providers[] = $provider;
    }
    public function providers(): array
    {
        return $this->providers;
    }

}
