<?php
namespace Simplydigital\EngineerManagement\Http\Components;
use Illuminate\View\Component;
class BladeSkillComponent extends Component
{
    public function render()
    {
        return view('engineermanagement::components.skills-component');
    }
}
