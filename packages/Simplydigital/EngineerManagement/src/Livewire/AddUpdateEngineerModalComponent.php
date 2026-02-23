<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Simplydigital\EngineerManagement\Models\EngineerManagement;
use Simplydigital\EngineerManagement\Services\EngineerManagementServices;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\DB;
use Random\Engine;

class AddUpdateEngineerModalComponent extends ModalComponent
{

    public $manager;
    public $user_id;
    public $department;
    public $position;
    public $active = true;

    public $users;
    public $managers;
    public $engineerManagerId;
    public $showEditModal = false;
    public $showAddModal = false;
    public $mode;
    public $modalMode = '';
    public $modalTitle = '';
    public $modalSave = 'save';
    public $listOfPositions = [];
    public $listOfDepartments = [];


    public static function maxWidth(): string
    {
        // Supported: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
        return '6xl';
    }

    public function rules()
    {
        if ($this->modalMode === 'toggleEditModal') {
            return [
                'manager' => 'required|integer',
                'position' => 'nullable|string|max:255',
                'department' => 'required|string|max:255',
                'active' => 'boolean',
            ];            
        } else {
            return [
                'manager' => 'required|integer',
                'user_id' => 'required|integer',
                'position' => 'nullable|string|max:255',
                'department' => 'required|string|max:255',
                'active' => 'boolean',
            ];
        }
    }

    public function mount(EngineerManagementServices $engineerManagementServices)
    {
        $this->modalMode = $this->mode;
        if ($this->engineerManagerId && $this->modalMode === 'toggleEditModal') {
            $this->modalSave = 'update';
            $this->retrieve();
        } else {
            $this->showAddModal = true;
        }
        $this->users = $this->getUsers($engineerManagementServices);
        $this->managers = $this->getManagers($engineerManagementServices);
        if ($this->managers->isEmpty()) {
            $this->managers = $this->users;
        }
        $this->listOfPositions = config('engineer-management.positions');
        $this->listOfDepartments = config('engineer-management.departments');
    }

    public function retrieve()
    {
        $engineerManager = EngineerManagement::find($this->engineerManagerId);
        $this->manager = $engineerManager->manager;
        $this->user_id = $engineerManager->user->name;
        $this->position = $engineerManager->position;
        $this->department = $engineerManager->department;
        $this->active = $engineerManager->active;
    }

    public function getUsers($engineerManagementServices)
    {
        $users = $engineerManagementServices->getNonEngineeringUsers();
        return $users;
    }

    public function getManagers($engineerManagementServices)
    {
        $managers = $engineerManagementServices->getEngineerManagers();
        return $managers;
    }

    public function save()
    {
        $this->validate();
        $inputs = [
            'number' => null,
            'manager' => $this->manager,
            'user_id' => $this->user_id,
            'position' => $this->position,
            'department' => $this->department,
            'active' => $this->active,
        ];
        EngineerManagement::create($inputs);
        session()->flash('message', 'User added to the Engineering Team.');
        $this->resetForm();
        $this->closedAndRefresh();
    }

    public function update()
    {
        $this->validate();
        $inputs = [
            'manager' => $this->manager,
            'position' => $this->position,
            'department' => $this->department,
            'active' => $this->active,
        ];
        $engineerManager = EngineerManagement::find($this->engineerManagerId);
        $engineerManager->update($inputs);
        session()->flash('message', 'Details have been successfully updated.');
        $this->resetForm();
        $this->closedAndRefresh();
    }

    public function toggleEditModal()
    {
        $this->closedAndRefresh();
    }

    public function toggleAddModal()
    {
        $this->closedAndRefresh();
    }

    public function closedAndRefresh()
    {
        $this->resetForm();
        $this->dispatch('closeModal');
        $this->closeModal();
    }

    private function resetForm()
    {
        $this->manager = '';
        $this->user_id = '';
        $this->position = '';
        $this->department = '';
    }

    public function render()
    {
        return view('engineermanagement::livewire.add-update-engineer-modal-component');
    }
}
