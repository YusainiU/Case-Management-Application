<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseServiceLevelAgreement;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Services\CaseSLAPService;

class ViewSlaImplementation extends Component
{

    use WithPagination;
    public ?int $slaId = null;
    public ?int $slapId = null;
    public ?int $customerId = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;
    public bool $displaySLAProfileModal = false;
    public bool $displayCustomerSummaryModal = false;
    
    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateSLAPModal' => 'closeCreateModal',
        'closeUpdateSLAPModal' => 'closeUpdateModal',
        'closeProfileSLAPModal' => 'closeProfileModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    protected function slapService(): CaseSLAPService
    {
        return app(CaseSLAPService::class);
    }

    public function closeSLAProfileModal()
    {
        $this->resetAllModal();
    }
    
    public function closeCreateModal()
    {
        $this->resetAllModal();  
    }

    public function closeUpdateModal()
    {        
        $this->resetAllModal();
    }

    public function closeProfileModal()
    {        
        $this->resetAllModal();
    }

    public function buildUI()
    {
        return [
            'title' => 'Service Level Agreement Implementation',
        ];
    }
    
    public function sortByField(?string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }    
    
    public function resetAllModal()
    {
        $this->reset([
            'displayProfileModal',
            'displayCreateModal',
            'displayUpdateModal',
            'displaySLAProfileModal',
            'displayCustomerSummaryModal',
        ]);
    }
    
    public function openCreateModal()
    {
        $this->resetAllModal();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal(int $slapId)
    {
        $this->resetAllModal();
        $this->slapId = (int) $slapId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal(int $slapId)
    {
        $this->resetAllModal();
        $this->slapId = $slapId;
        $this->displayProfileModal = true;
    }

    public function openSLAProfileModal(string|int $slaId)
    {
        $this->resetAllModal();
        $this->slaId = (int) $slaId;
        $this->displaySLAProfileModal = true;
    }

    public function openCustomerSummaryModal(int $customerId)
    {
        $this->resetAllModal();
        $this->customerId = $customerId;
        $this->displayCustomerSummaryModal = true;
    }
    
    public function loadSLAP()
    {
        $service = $this->slapService();
        if($this->customerId){
            $service->setCustomer($this->customerId);
        }        
        if($this->sortField){
            $service->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $service->filterResult($this->filter);
        }        
        $query = $service->getActiveSLAP();
        return $query;        
    }
    public function render()
    {
        $ui = $this->buildUI();
        $slaps = $this->loadSLAP()->paginate(10);        
        return view('casemanagement::livewire.service-level-agreement.view-sla-implementation',[
            'ui_params' => $ui,
            'slaps' => $slaps,
        ]);
    }
}