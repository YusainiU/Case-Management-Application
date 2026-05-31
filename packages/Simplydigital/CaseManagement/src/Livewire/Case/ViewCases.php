<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
class ViewCases extends Component
{
    use WithPagination;
    public ?int $customerId = null;
    public ?int $caseId = null;
    public ?Collection $tickets = null;
    public ?tickets $ticket = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;    
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;
    public bool $displaySummaryModal = false;
    public bool $displayDescription = false;
    public bool $displaySlaStatus = false; 
    public array $descriptionDetails = []; 
    public ?string $displayCaseStatus = null;    

    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateCaseModal' => 'closeCreateModal',
        'closeUpdateCaseModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
        'closeSummaryModal' => 'closeSummaryModal',
    ];

    public function mount()
    {
        $this->displayCaseStatus = CaseState::Open->value;
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
            'displaySummaryModal',
            'displayDescription',
            'displaySlaStatus',
        ]);
    }

    public function resetAllFiltersAndSorting()
    {
        $this->reset([
            'filter',
            'sortField',
            'sortDirection',
            'displayCaseStatus',
            'displayDescription',
            'descriptionDetails',
        ]);
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
    
    public function closeSummaryModal()
    {        
        $this->resetAllModal();
    }

    public function closeDescriptionModal()
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

    public function openDescription(int|string $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $ticket = $this->tickets->firstWhere('id', $caseId);
        $siteName = $ticket?->customer?->name;
        $description = $ticket?->description;
        $this->descriptionDetails = [
            'name' => $siteName,
            'description' => $description,
        ];
        $this->displayDescription = true;
    }

    public function openSlaStatus(int|string $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $this->ticket = $this->tickets->firstWhere('id', $caseId);
        $this->displaySlaStatus = true;
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
    
    public function openSummaryModal(int $caseId)
    {
        $this->resetAllModal();
        $this->caseId = $caseId;
        $this->displaySummaryModal = true;
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
    
    public function openTab(CaseState $status)
    {
        $this->resetAllFiltersAndSorting();
        $this->displayCaseStatus = $status->value;
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

        if($this->displayCaseStatus == CaseState::Open->value) {
            $query = $service->getAllOpenCases();
        }
        
        if($this->displayCaseStatus === CaseState::In_Progress->value) {
            $query = $service->getOutstandingCases();
        }

        if($this->displayCaseStatus === CaseState::Resolved->value) {
            $query = $service->getAllOpenResolvedCases();
        }
        
        if($this->displayCaseStatus === CaseState::Completed->value) {
            $query = $service->getAllClosedResolvedCases();
        }

        if($this->displayCaseStatus === CaseState::Closed->value) {
            $query = $service->getAllClosedCases();
        }

        if($this->displayCaseStatus === CaseState::Paused->value) {
            $query = $service->getAllPausedCases();
        }

        if(isset($query)){
            $this->tickets = $query->get();
            return $query;
        }

        return null;
        
    }

    public function render()
    {
        $cases = null;
        $ui = $this->buildUI();
        $cases = $this->loadCases()?->paginate(10);
        return view('casemanagement::livewire.case.view-cases',[
            'ui_params' => $ui,
            'cases' => $cases,
        ]);
               
    }
    
    

}