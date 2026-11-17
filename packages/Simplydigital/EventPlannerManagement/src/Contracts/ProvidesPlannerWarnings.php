<?php

namespace Simplydigital\EventPlannerManagement\Contracts;

interface ProvidesPlannerWarnings
{
    /** @return list<string> */
    public function warnings(): array;
}
