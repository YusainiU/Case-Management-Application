<?php

namespace Simplydigital\EventPlannerManagement\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\EventPlannerManagement\Support\DateRange;

interface PlannerEventRepository
{
    /**
     * @return Collection<int, Model>
     */
    public function all(?DateRange $range = null): Collection;

    public function find(string $id): ?Model;

    public function save(PlannerEvent $event): Model;

    public function delete(string $id): bool;
}
