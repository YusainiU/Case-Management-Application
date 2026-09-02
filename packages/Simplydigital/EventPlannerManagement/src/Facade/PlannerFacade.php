<?php

namespace Simplydigital\EventPlannerManagement\Facade;

use Illuminate\Support\Facades\Facade;
use Simplydigital\EventPlannerManagement\Services\PlannerService;

class PlannerFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PlannerService::class;
    }
}