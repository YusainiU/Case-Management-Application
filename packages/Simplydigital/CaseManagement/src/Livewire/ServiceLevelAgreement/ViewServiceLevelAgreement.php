<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use Livewire\Component;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;
use Simplydigital\CaseManagement\Services\CaseServiceLevelAgreement;
use Livewire\WithPagination;

class ViewServiceLevelAgreement extends Component
{
    use WithPagination;
    public ?int $slaId = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;

    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateSLAModal' => 'closeCreateModal',
        'closeUpdateSLAModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    protected function slaService(): CaseServiceLevelAgreement
    {
        return app(CaseServiceLevelAgreement::class);
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
    
    public function resetAllModal()
    {
        $this->reset([
            'displayProfileModal',
            'displayProductProfile',
            'displayCustomerProfile',
            'displaySupplierProfile',
        ]);
    }    

    public function buildUI()
    {
        return [
            'title' => 'Service Level Agreement (SLA)',
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
    
    public function openCreateModal()
    {
        $this->resetAllModal();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal(int $slaId)
    {
        $this->resetAllModal();
        $this->slaId = $slaId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal(int $slaId)
    {
        $this->resetAllModal();
        $this->slaId = $slaId;
        $this->displayProfileModal = true;
    }
    
    public function loadSLAS()
    {
        $service = $this->slaService();
        if($this->sortField){
            $service->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $service->filterResult($this->filter);
        }        
        $query = $service->getActiveSLA();
        return $query;        
    }

    public function render()
    {
        $ui = $this->buildUI();
        $slas = $this->loadSLAS()->paginate(10);
        return view('casemanagement::livewire.serviceLevelAgreement.view-service-level-agreement', [
            'slas' => $slas,
            'ui_params' => $ui,
        ]);
    }

}