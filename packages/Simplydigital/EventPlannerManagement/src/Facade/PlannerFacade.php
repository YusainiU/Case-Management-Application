<?php

namespace Simplydigital\EventPlannerManagement\Facade;

use Illuminate\Support\Facades\Facade;

class PlannerFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'planner';
    }
}