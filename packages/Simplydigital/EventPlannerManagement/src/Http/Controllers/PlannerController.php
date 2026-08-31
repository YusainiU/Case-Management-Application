<?php

namespace Simplydigital\EventPlannerManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PlannerController extends Controller
{
    public function index(Request $request)
    {
        return view('eventplanner::livewire.planner', [
            'view' => $request->query('view', config('planner.default_view', 'week')),
            'events' => [],
        ]);
    }
}
