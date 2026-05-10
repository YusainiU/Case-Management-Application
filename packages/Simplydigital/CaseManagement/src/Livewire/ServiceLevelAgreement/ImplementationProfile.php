<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Services\CaseSLAPService;
use Simplydigital\CaseManagement\Models\slaApplication;

class ImplementationProfile extends ModalComponent
{

    public string $title = "SLA Implementation Profile";
    public ?int $slapId = null;
    public ?slaApplication $slap = null;
    public array $slapItems = [];

    public function mount()
    {
        $this->loadSLAP();
    }

    protected function slapService(): CaseSLAPService
    {
        return app(CaseSLAPService::class);
    }

    public function loadSLAP()
    {
        $service = $this->slapService();
        $this->slap = $service->getSLAPById($this->slapId);
        $this->slapItems = $service->buildProfileItems($this->slap);
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');   
    }          

    public function render()
    {
        return view('casemanagement::livewire.service-level-agreement.implementation-profile');
    }
}