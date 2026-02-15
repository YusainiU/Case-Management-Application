<?php

namespace Simplydigital\UserManagement\Livewire;

use Livewire\WithPagination;
use LivewireUI\Modal\ModalComponent;
use Spatie\Permission\Models\Permission;

class ListPermissions extends ModalComponent
{

    use WithPagination;

    public $name;
    public $filter = '';
    public $perPage = 5;
    public $showList = true;
    public $showAddPermission = false;
    public $confirmCall = 'displayCreateForm';
    public $confirmText = 'Create New Permission';
    public $defaultGuardName = 'web';

 
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        
    }

    public function backToList()
    {
        $this->resetDisplay();
    }

    public function resetDisplay()
    {
        $this->showList = true;
        $this->showAddPermission = false;
        $this->confirmCall = 'displayCreateForm';
        $this->confirmText = 'Create New Permission';
        $this->name = '';
    }

    public function displayCreateForm()
    {
        $this->showList = false;
        $this->showAddPermission = true;
        $this->confirmText = 'confirm';
        $this->confirmCall = 'createNewPermission';
    }

    public static function modalMaxWidth(): string
    {
        return '6xl';
    }

    public function closeThis()
    {
        $this->resetDisplay();
        $this->dispatch('closePermissionModal');
    }    

    public function createNewPermission()
    {
        $this->defaultGuardName = config('auth.defaults.guard');
        $this->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);
        $input = [
            'name' => $this->name,
            'guard_name' => $this->defaultGuardName,
        ];
        Permission::create($input);
        $this->resetDisplay();
        $this->updatingSearch();

    }

    public function render()
    {
        $roles = Permission::query()
            ->when($this->filter, function ($query) {
                $query->where('name', 'like', '%' . $this->filter . '%')
                    ->orWhere('guard_name', 'like', '%' . $this->filter . '%');
            })
            ->paginate($this->perPage);

        return view('usermanagement::livewire.list-permissions', [
            'permissions' => $roles,
        ]);
    }
}