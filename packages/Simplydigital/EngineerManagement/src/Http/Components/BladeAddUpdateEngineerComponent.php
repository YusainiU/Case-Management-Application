<?php

namespace Simplydigital\EngineerManagement\Http\Components;
use Illuminate\View\Component;
class BladeAddUpdateEngineerComponent extends Component
{
    public function render()
    {
        return view('engineermanagement::components.add-update-engineer-modal-component');
    }
}
