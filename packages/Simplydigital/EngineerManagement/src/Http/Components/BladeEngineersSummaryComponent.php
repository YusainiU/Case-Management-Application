<?php

namespace Simplydigital\EngineerManagement\Http\Components;
use Illuminate\View\Component;

class BladeEngineersSummaryComponent extends Component
{
    public function render()
    {
        return view('engineermanagement::components.engineers-summary-component');
    }
}