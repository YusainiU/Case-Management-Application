<?php

namespace Simplydigital\EventPlannerManagement\Http\Controllers;

use Illuminate\View\View;

class PlannerController
{
    public function index(): View 
    { 
        return view('eventplanner::planner'); 
    }
}
