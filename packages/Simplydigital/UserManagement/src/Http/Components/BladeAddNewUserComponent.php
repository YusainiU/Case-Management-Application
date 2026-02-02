<?php

Namespace Simplydigital\UserManagement\Http\Components;
use Illuminate\View\Component;
Class BladeAddNewUserComponent extends Component
{
    public function render()
    {
        return view('usermanagement::components.add-new-user');
    }
}