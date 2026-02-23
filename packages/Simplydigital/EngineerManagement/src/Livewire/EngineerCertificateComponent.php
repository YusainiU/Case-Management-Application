<?php

Namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Simplydigital\EngineerManagement\Models\Certificates;
use Simplydigital\EngineerManagement\Models\EngineerCertifications;
use Livewire\Attributes\On;

class EngineerCertificateComponent extends Component
{

    public $certificateId;
    public $engineerCertificateId;
    public $engineerId;
    public $engineerWithCertificates;
    public $certificate;
    public $displayEngineerCertificateDetails = false;

    public function mount()
    {
        $this->initialise();
    }

    public function initialise()
    {
        $this->engineerWithCertificates = $this->getEngineersWithCertificate();
        $this->certificate = $this->getCertificate();
    }

    #[On('closedAddUpdateEngineerCertificateModalComponent')]
    public function refreshEngineerCertifications()
    {
        $this->resetCertificateDetails();        
    }
    
    public function resetCertificateDetails()
    {
        $this->engineerCertificateId = null;
        $this->displayEngineerCertificateDetails = false;
        $this->initialise();
    }

    public function getEngineersWithCertificate()
    {
        return EngineerCertifications::where('certificate_id', '=',$this->certificateId)->get(); 
    }

    public function getCertificate()
    {
        return Certificates::find($this->certificateId);
    }

    public function closeComponent()
    {
        $this->dispatch('closeEngineerSummaryComponent');
    }
    
    public function openEngineerCertificate($engineercertificateId, $engineerid)
    {
        $this->engineerCertificateId = $engineercertificateId;
        $this->engineerId = $engineerid;
        $this->displayEngineerCertificateDetails = true;
    }

    public function render()
    {
        return view('engineermanagement::livewire.engineer-certificate-component');
    }

}