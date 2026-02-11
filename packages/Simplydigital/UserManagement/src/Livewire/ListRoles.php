<?php

namespace Simplydigital\UserManagement\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ListRoles extends Component
{


    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->search . '%')
            ->paginate(15);

        return view('usermanagement::livewire.list-roles', [
            'roles' => $roles,
        ]);
    }
}