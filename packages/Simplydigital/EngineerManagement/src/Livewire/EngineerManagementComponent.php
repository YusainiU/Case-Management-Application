<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Simplydigital\EngineerManagement\Models\EngineerManagement;
use Livewire\WithPagination;

class EngineerManagementComponent extends Component
{

    use WithPagination;

    
    public $engineerManagerId;
    public $engineerId;
    public $modalMode = '';
    public $modalSkill = false;
    public $modalCertificate = false;
    public $modalTitle = '';
    public $showEditModal = false;
    public $showAddModal = false;
    public $filter = '';
    

    protected $listeners = [
        'closeModal',
    ];

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->modalMode = '';
        $this->modalTitle = '';
        $this->modalSkill = false;
        $this->modalCertificate = false;
        $this->render();
    }
    

    public function delete($id)
    {
        $engineerManager = EngineerManagement::find($id);
        $engineerManager->delete();
        session()->flash('message', 'User has been removed from the engineering team.');
    }

    public function retrieve()
    {
        return EngineerManagement::find($this->engineerManagerId);
        
    }    


    public function filter()
    {
        $query = EngineerManagement::query();
        //$query->where('active','=',true);

        if($this->filter)
        {
            $filterValue = "%{$this->filter}%";
            $query->whereAny(['position','department'],'like',$filterValue);
            $query->orWhereHas('user', function ($q) use ($filterValue) {
                $q->where('name', 'like', $filterValue)
                  ->orWhere('email', 'like', $filterValue);
            });
            $query->orWhereHas('engmanager', function ($q) use ($filterValue) {
                $q->where('name', 'like', $filterValue)
                  ->orWhere('email', 'like', $filterValue);
            });
        }        
        return $query;
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function toggleEditModal($id = null)
    {
        if($id){
            $this->engineerManagerId = $id;
            $engineer = $this->retrieve();
        }
        $this->showEditModal = !$this->showEditModal;
        $this->modalTitle = $this->showEditModal ? 'Edit Engineer Details':'';
        $this->modalMode = $this->showEditModal ? 'toggleEditModal':'';
    }

    public function toggleAddModal()
    {
        $this->showAddModal = !$this->showAddModal;
        $this->modalTitle = $this->showAddModal ? 'Add Engineer':'';
        $this->modalMode = $this->showAddModal ? 'toggleAddModal':'';
    }

    public function toggleSkillModal($id = null)
    {
        $this->engineerId = $id;
        $this->modalSkill = !$this->modalSkill;
        $this->modalTitle = $this->modalSkill ? 'Engineer Skills':'';
    }
    
    public function toggleCertificateModal($id = null)
    {
        $this->engineerId = $id;
        $this->modalCertificate = !$this->modalCertificate;
        $this->modalTitle = $this->modalSkill ? 'Engineer Certificates':'';
    }

    public function render()
    {
        $query = $this->filter();
        $showList = $query->exists() ? true : false;
        if($showList)
        {
            $engineers = $query->paginate(10, pageName:'engineer-manager-page');
        }else{
            $engineers = [];
        }

        return view('engineermanagement::livewire.engineer-management-component',[
            'engineers' => $engineers,
            'showList' => $showList,
        ]);
    }
}
