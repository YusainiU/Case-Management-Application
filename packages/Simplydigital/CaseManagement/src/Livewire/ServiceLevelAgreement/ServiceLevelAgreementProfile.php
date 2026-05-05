<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;
use Simplydigital\CaseManagement\Services\CaseServiceLevelAgreement;

class ServiceLevelAgreementProfile extends ModalComponent
{

    public string $title = "Service Level Agreement Profile";
    public ?int $slaId = null;
    public ?serviceLevelAgreements $sla = null;
    public array $slaProfile = [];

    protected function slaService(): CaseServiceLevelAgreement
    {
        return app(CaseServiceLevelAgreement::class);
    }
    
    public function mount()
    {
        $this->loadSLA();
    }

    public function loadSLA()
    {
        $service = $this->slaService();
        $this->sla = $service->getSLAById($this->slaId);
        $this->slaProfile = $service->buildProfileItems($this->sla);
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');   
    }      

    public function render()
    {
        return view('casemanagement::livewire.service-level-agreement.service-level-agreement-profile');
    }
}