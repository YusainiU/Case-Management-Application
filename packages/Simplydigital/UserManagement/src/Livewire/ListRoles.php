<?php

namespace Simplydigital\UserManagement\Livewire;

use Livewire\WithPagination;
use LivewireUI\Modal\ModalComponent;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ListRoles extends ModalComponent
{


    use WithPagination;

    public string $filter = '';
    public $roleName;
    public $name;
    public $selectedRole;
    public $selectedPermissions = [];
    public $paginate = 5;
    public $showList = true;
    public $showAddRole = false;
    public $showUpdateRole = false;
    public $confirmCall = 'displayCreateForm';
    public $confirmText = 'Create New Role';
    public $defaultGuardName = 'web';
    public $allPermissions = [];


    public function mount()
    {
        $this->allPermissions = Permission::all()->pluck('name', 'id')->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function backToList()
    {
        $this->resetDisplay();
    }

    public function resetDisplay()
    {
        $this->showList = true;
        $this->showAddRole = false;
        $this->showUpdateRole = false;
        $this->confirmCall = 'displayCreateForm';
        $this->confirmText = 'Create New Role';
        $this->name = '';
        $this->selectedPermissions = [];
        $this->roleName = null;
    }
    
    public function displayCreateForm()
    {
        $this->showList = false;
        $this->showAddRole = true;
        $this->confirmText = 'confirm';
        $this->confirmCall = 'createNewRole';
    }

    public function displayUpdateForm($roleName)
    {
        $this->showList = false;
        $this->showAddRole = false;
        $this->roleName = $roleName;
        $this->showUpdateRole = true;
        $this->confirmText = 'confirm';
        $this->confirmCall = 'updateExistingRolePermission';
        $this->retrieveRole();
    }

    public function retrieveRole()
    {
        $this->selectedRole = Role::findByName($this->roleName);
        $this->selectedPermissions = $this->selectedRole->permissions()->pluck('name')->toArray();
    }

    public function updateExistingRolePermission()
    {
        $this->validate([
            'selectedPermissions' => 'array',
        ]);
        $this->selectedRole->syncPermissions($this->selectedPermissions);
        $this->resetDisplay();
        $this->updatingSearch();        
    }
    
    public function createNewRole()
    {
        $this->validate([
            'name' => 'required|string|unique:roles,name',
            'selectedPermissions' => 'array',
        ]);
        $role = Role::create(['name' => $this->name,'guard_name' => $this->defaultGuardName]);        
        $role->syncPermissions($this->selectedPermissions);
        $this->resetDisplay();
        $this->updatingSearch();
    }

    public function closeThis()
    {
        $this->resetDisplay();
        $this->dispatch('closeRoleModal');
    }

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->filter . '%')
            ->paginate($this->paginate);

        return view('usermanagement::livewire.list-roles', [
            'roles' => $roles,
        ]);
    }
}