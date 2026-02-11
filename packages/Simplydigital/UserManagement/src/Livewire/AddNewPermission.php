<?php

namespace Simplydigital\UserManagement\Livewire;

use Illuminate\Database\Eloquent\Model;
use LivewireUI\Modal\ModalComponent;

class AddNewPermission extends ModalComponent
{
    public $permissionName = '';
    public $permissionDescription = '';
    public $showModal = false;

    protected $rules = [
        'permissionName' => 'required|string|min:3|max:255|unique:permissions,name',
        'permissionDescription' => 'nullable|string|max:500',
    ];

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeForm()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirm()
    {
        $this->validate();

        // Assuming you have a Permission model
        $permissionModel = config('permission.models.permission');
        
        $permissionModel::create([
            'name' => $this->permissionName,
            'description' => $this->permissionDescription,
            'guard_name' => 'web',
        ]);

        $this->dispatch('permission-added');
        $this->closeModal();
    }

    public function resetForm()
    {
        $this->permissionName = '';
        $this->permissionDescription = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('usermanagement::livewire.add-new-permission');
    }
}