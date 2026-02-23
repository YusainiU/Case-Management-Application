<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Simplydigital\EngineerManagement\Models\Certificates;
use Livewire\WithPagination;

class CertificateComponent extends Component
{

    use WithPagination;

    
    public $filter = '';
    public $modalMode = ''; // 'add' or 'update'
    public $modalTitle = '';
    public $certificateId = null;
    public $showList = false;

    protected $listeners = [
        'certificateSaved' => 'loadCertificates',
        'closeModal' => 'closeModal',
    ];

    public function mount()
    {
        $this->loadCertificates();
    }

    public function loadCertificates()
    {
        $certificates = Certificates::all();
        if($certificates->isEmpty()) {
            $this->showList = false; 
        } else {
            $this->showList = true;
        }
        $this->modalMode = '';
        $this->render();
    }

    public function closeModal()
    {
        $this->modalMode = '';
    }

    public function toggleAddModal()
    {
        $this->modalMode = 'add';
        $this->modalTitle = 'Add Certificate';
    }

    public function toggleUpdateModal($id)
    {
        $this->certificateId = $id;
        $this->modalMode = 'update';
        $this->modalTitle = 'Update Certificate';
    }

    public function filter()
    {
        $query = Certificates::query();
        if($this->filter)
        {
            $filterValue = "%{$this->filter}%";
            $query->where('name', 'like', $filterValue)
                  ->orWhere('issued_by', 'like', $filterValue);
        }        
        return $query;
    }

    public function render()
    {
        $certificates = $this->filter()->orderBy('created_at', 'desc');
        $this->showList = $certificates->exists() ? true : false;
        return view('engineermanagement::livewire.certificate-component', [
            'certificates' => $certificates->paginate(10,pageName:'certificates-page'),
        ]);
    }
}      