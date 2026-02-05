<?php

Namespace Simplydigital\UserManagement\Http\Components;
use Illuminate\View\Component;
Class BladeListAllUserComponent extends Component
{
    public function render()
    {
        return view('usermanagement::components.list-all-user');
    }
}