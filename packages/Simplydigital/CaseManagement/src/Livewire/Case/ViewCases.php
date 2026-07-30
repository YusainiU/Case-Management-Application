<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\Customer;
use Livewire\Attributes\Url;
class ViewCases extends Component
{
    use WithPagination;
    public ?int $customerId = null;
    public ?int $caseId = null;
    public ?Collection $tickets = null;
    public ?tickets $ticket = null;
    public ?Customer $customer = null;
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
    public bool $displayCustomerModal = false;

    #[Url]
    public ?string $dashboardFilter = null;

    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateCaseModal' => 'closeCreateModal',
        'closeUpdateCaseModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
        'closeSummaryModal' => 'closeSummaryModal',
        'reloadCaseStatus' => '$refresh',
        'AllClosedCases' => 'openAllClosedCases',
        'AllPausedCases' => 'openAllPausedCases',
        'ResetFilterSelection' => 'resetFilterSelection',
        'AllResolvedCases' => 'openAllResolvedCases',
        'AllNearBreachedCases' => 'openAllNearBreachedCases',
        'AllBreachedCases' => 'openAllBreachedCases',
        'AllUnassignedCases' => 'openAllUnassignedCases',
        'AllNoUpdateCases' => 'openAllNoUpdateCases',
        'AllOpenCases' => 'resetFilterSelection',

    ];

    public function mount()
    {        
        $this->displayCaseStatus = $this->dashboardFilter ?? CaseState::Open->value;
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
            'displayCustomerModal',
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

    public function openAllResolvedCases()
    {
        $this->displayCaseStatus = CaseState::Resolved->value;
    }

    public function openAllNearBreachedCases()
    {
        $this->displayCaseStatus = 'nearBreached';
    }

    public function openAllBreachedCases()
    {
        $this->displayCaseStatus = 'breached';
    }

    public function openAllUnassignedCases()
    {
        $this->displayCaseStatus = 'unassigned';
    }

    public function openAllCompletedCases()
    {
        $this->displayCaseStatus = CaseState::Completed->value;
    }

    public function openAllClosedCases()
    {
        $this->displayCaseStatus = CaseState::Closed->value;
    }

    public function openAllPausedCases()
    {
        $this->displayCaseStatus = CaseState::Paused->value;
    }

    public function openAllNoUpdateCases()
    {
        $this->displayCaseStatus = 'noUpdate';
    }

    public function resetFilterSelection()
    {
        $this->displayCaseStatus = CaseState::Open->value;
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
        $column = 'id';
        $ticket = $this->tickets->firstWhere($column, $caseId);
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
        $column = "id";
        $this->ticket = $this->tickets->firstWhere($column, $caseId);
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

    public function openCustomerSummaryModal(int $customerId)
    {
        $this->resetAllModal();
        $this->customer = Customer::find($customerId);
        $this->displayCustomerModal = true;
    }

    public function openProfileModal(int $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $column = "id";
        $this->ticket = $this->tickets->firstWhere($column, $caseId);
        if(!$this->ticket){
            $this->ticket = $this->ticketService()->getCaseById($caseId);            
        }        
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
        $filterString = $this->filter ?? null;
        $query = null;
        
        if($this->sortField){
            $service->sortByField($this->sortField, $this->sortDirection);
        }

        if($this->customerId) {
            $service->setCustomer($this->customerId);
        }

        if($this->displayCaseStatus == 'breached' ) {
            $query = $service->getAllBreachedCases($filterString);
        }

        if($this->displayCaseStatus == 'nearBreached') {
            $query = $service->getAllNearBreachCases($filterString);
        }

        if($this->displayCaseStatus == 'unassigned') {
            $query = $service->getAllUnassignedCases($filterString);
        }

        if($this->displayCaseStatus == 'noUpdate') {
            $query = $service->getAllNoUpdateCases($filterString);
        }

        if($this->displayCaseStatus == CaseState::Open->value) {
            $query = $service->getAllOpenCases($filterString);
        }
        
        if($this->displayCaseStatus === CaseState::In_Progress->value) {
            $query = $service->getOutstandingCases($filterString);
        }

        if($this->displayCaseStatus === CaseState::Resolved->value) {
            $query = $service->getAllOpenResolvedCases($filterString);
        }
        
        if($this->displayCaseStatus === CaseState::Completed->value) {
            $query = $service->getAllClosedResolvedCases($filterString);
        }

        if($this->displayCaseStatus === CaseState::Closed->value) {
            $query = $service->getAllClosedCases($filterString);
        }

        if($this->displayCaseStatus === CaseState::Paused->value) {
            $query = $service->getAllPausedCases($filterString);
        }

        if(isset($query)){
            $this->tickets = $query->orderByDesc('created_at')->get();
            return $query;
        }

        return null;
        
    }

    public function render()
    {
        $cases = null;
        $ui = $this->buildUI();
        $cases = $this->loadCases()?->paginate(10);
        $hideStatus = $this->customerId || $this->dashboardFilter;
        return view('casemanagement::livewire.case.view-cases',[
            'ui_params' => $ui,
            'cases' => $cases,
            'hideStatus' => $hideStatus,
        ]);
               
    }
    
    

}