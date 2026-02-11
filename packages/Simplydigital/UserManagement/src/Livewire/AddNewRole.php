<?php

namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddNewRole extends ModalComponent
{
    public $showModal = false;
    public $roleName = '';
    public $selectedPermissions = [];
    public $allPermissions = [];

    public function mount()
    {
        $this->allPermissions = Permission::all()->pluck('name', 'id')->toArray();
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeForm()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->roleName = '';
        $this->selectedPermissions = [];
    }

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|string|unique:roles,name',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::create(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('roleCreated', message: 'Role created successfully');
        $this->closeModal();
    }

    public function render()
    {
        return view('usermanagement::livewire.add-new-role');
    }
}