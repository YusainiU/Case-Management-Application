<?php

namespace Simplydigital\EngineerManagement\Http\Components;
use Illuminate\View\Component;
class BladeEngineerManagementComponent extends Component
{
    public function render()
    {
        return view('engineermanagement::components.engineer-management');
    }
}
