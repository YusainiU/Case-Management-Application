<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Services\CaseSLAPService;

class SlaImplementationProfile extends ModalComponent
{
    // public string $title = "Service Level Agreement Application";
    // public ?int $slapId = null;
    // public ?slaApplication $slap = null;
    // public array $slapItems = [];

//     protected function slapService(): CaseSLAPService
//     {
//         return app(CaseSLAPService::class);
//     }

//     public function mount()
//     {
//         $this->loadSLAP();
//     }    
    
//     public function loadSLAP()
//     {
//         $service = $this->slapService();
//         $this->slap = $service->getSLAPById($this->slapId);
//         $this->slapItems = $service->buildProfileItems($this->slap);
//     }
    
//    public function closeThisModal()
//     {
//         $this->dispatch('closeProfileModal');   
//     }       

    public function render()
    {
        view('casemanagement::livewire.service-level-agreement.sla-implementation-profile');
    }
}

