<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Simplydigital\EngineerManagement\Models\EngineerCertifications;
use Simplydigital\EngineerManagement\Models\EngineerManagement;
use LivewireUI\Modal\ModalComponent;
use Simplydigital\EngineerManagement\Models\Certificates;

class AddUpdateEngineerCertificateModalComponent extends ModalComponent
{

    public $engineerId;
    public $engineer;
    public $engineerCertificateId = null;
    public $certificateId;
    public $expiryDate = null;
    public $issuedDate = null;
    public $expired = false;
    public $active = true;

    public $modalMode='closedAndRefresh'; // 'add' or 'update'
    public $modalTitle='Engineer Certificates';
    public $modalSave = 'save';
    public $certificates;
    public $engineerCertificate;
    public $allCertificates;
    public $displayList = true;
    public $originExternalForUpdate = false;

    public function rules()
    {
        return [
            'certificateId' => 'required|integer',
            'active' => 'boolean',
            'issuedDate' => 'nullable|date',
            'expiryDate' => 'nullable|date',
        ];
    }

    public function maxWidth(): string
    {           
        // Supported: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
        return '6xl';
    }

    public function mount()
    {
        if($this->originExternalForUpdate)
        {
            $this->updateCertificate($this->engineerCertificateId);
        }else{
            $this->initialise();            
        }        
    }

    public function initialise()
    {
        $this->engineer = $this->getEngineer();
        $this->certificates = $this->engineer->certifications;
        $this->allCertificates = $this->getAllActiveCertificates();
        if($this->engineerCertificateId)
        {
            $this->engineerCertificate = $this->getEngineerCertificate();
            $this->certificateId = $this->engineerCertificate->certificate_id;
            $this->issuedDate = $this->engineerCertificate->issued_date?->format('Y-m-d');
            $this->expiryDate = $this->engineerCertificate->expiry_date?->format('Y-m-d');
            $this->active = $this->engineerCertificate->active;
        }
    }

    public function getEngineerCertificate()
    {
        return EngineerCertifications::find($this->engineerCertificateId);
    }
    
    public function getEngineer()
    {
        return EngineerManagement::find($this->engineerId);
    }

    public function getAllActiveCertificates()
    {
        return Certificates::where('active', true)->get();
    }

    public function update()
    {
        $this->validate();
        $input = [
            'engineer_id' => $this->engineerId,
            'certificate_id' => $this->certificateId,
            'issued_date' => $this->issuedDate,
            'expiry_date' => $this->expiryDate,
            'active' => $this->active,
        ];
        $this->engineerCertificate->update($input);
        session()->flash('message', 'Engineer Certificate updated successfully.');
        $this->resetForm();
        $this->closedAndRefresh();
    }

    public function save()
    {
        $this->validate();
        $input = [
            'certification_number' => null,
            'engineer_id' => $this->engineerId,
            'certificate_id' => $this->certificateId,
            'expiry_date' => $this->expiryDate,
            'issued_date' => $this->issuedDate,
            'active' => $this->active,
        ];
        EngineerCertifications::create($input);
        session()->flash('message', 'Engineer Certificate added successfully.');
        $this->resetForm();
        $this->closedAndRefresh();
    }   

    public function closedAndRefresh()
    {
        $this->dispatch('closedAddUpdateEngineerCertificateModalComponent');
        $this->closeModal();
    }

    public function resetForm()
    {
        $this->engineerCertificateId = null;
        $this->certificateId = null;
        $this->expiryDate = null;
        $this->issuedDate = null;
        $this->active = true;
        $this->modalSave = 'save';
    }

    public function createNewCertificate()
    {
        $this->modalSave = 'save';
        $this->displayList = false; 
    }

    public function updateCertificate($certificateId)
    {
        $this->resetForm();
        $this->engineerCertificateId = $certificateId;
        $this->initialise();
        $this->displayList = false;
        $this->modalSave = 'update';    
    }

    public function backToList()
    {
        $this->resetForm();
        $this->displayList = true;
    }
    
    public function render()
    {
        return view('engineermanagement::livewire.add-update-engineer-certificate-modal-component');
    }

}
    