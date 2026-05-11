<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\UI;
class ViewCases extends Component
{
    use WithPagination;
    public ?int $customerId = null;
    public ?int $caseId = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;    
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;
    public ?string $displayCaseStatus = null;    

    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateCaseModal' => 'closeCreateModal',
        'closeUpdateCaseModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    public function mount()
    {
        $this->displayCaseStatus = UI::OUTSTANDING_CASE;
    }

    protected function ticketService(): CaseTicketServices
    {
        return app(CaseTicketServices::class);
    }

    public function resetAllModal()
    {
        $this->reset([
            'displayProfileModal',
            'displayCreateModal',
            'displayUpdateModal',
        ]);
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
            'title' => 'Cases',
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

    public function openUpdateModal(int $caseId)
    {
        $this->resetAllModal();
        $this->caseId = $caseId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal(int $caseId)
    {
        $this->resetAllModal();
        $this->caseId = $caseId;
        $this->displayProfileModal = true;
    }

    public function changeDisplayTo(?string $status)
    {
        $this->displayCaseStatus = $status;   
    }
    
    public function loadCases()
    {
        $service = $this->ticketService();

        if($this->sortField){
            $service->sortByField($this->sortField, $this->sortDirection);
        }

        if($this->filter){
            $service->filterResult($this->filter);
        }

        if($this->customerId) {
            $service->setCustomer($this->customerId);
        }

        if($this->displayCaseStatus == UI::OPEN_CASE) {
            return $query = $service->getAllOpenCases();
        }
        
        if($this->displayCaseStatus === UI::OUTSTANDING_CASE) {
            return $query = $service->getAllOpenCases();
        }

        if($this->displayCaseStatus === UI::OPEN_CASE) {
            return $query = $service->getAllOpenCases();
        }
        
        if($this->displayCaseStatus === UI::RESOLVED_CASE) {
            return $query = $service->getAllOpenResolvedCases();
        }        
        
    }

    public function render()
    {
        $ui = $this->buildUI();
        $cases = $this->loadCases()->paginate(10);
        return view('casemanagement::livewire.case.view-cases',[
            'ui_params' => $ui,
            'cases' => $cases,
        ]);
               
    }
    
    

}