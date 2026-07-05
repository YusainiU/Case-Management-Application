<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Services\CaseSiteVisitService;
use Simplydigital\CaseManagement\Models\tickets;

class ViewSiteVisit extends Component
{

    use WithPagination;
    public ?TaskSiteVisit $visit = null;
    public ?TaskSiteVisit $selectedVisit = null;
    public ?int $customerId = null;
    public ?tickets $case = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public string $sortDirection = 'desc';
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;
    public bool $displaySummaryModal = false;    


    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateVisitModal' => 'closeCreateModal',
        'closeUpdateVisitModal' => 'closeUpdateModal',
        'closeVisitProfileModal' => 'closeProfileModal',
        'closeVisitSummaryModal' => 'closeSummaryModal',
        'reloadVisitStatus' => '$refresh',
    ];

    public function closeCreateModal()
    {
        $this->resetAllModals();
    }

    public function closeUpdateModal()
    {
        $this->resetAllModals();
    }

    public function closeProfileModal()
    {
        $this->resetAllModals();
    }

    public function closeSummaryModal()
    {
        $this->resetAllModals();
    }

    public function resetAll()
    {
        $this->resetAllFilterAndSorting();
        $this->resetAllModals();
    }

    public function resetAllFilterAndSorting()
    {
        $this->reset([
            'filter',
            'sortField',
            'sortDirection',
        ]);
    }

    public function resetAllModals()
    {
        $this->reset([
            'displayProfileModal',
            'displayCreateModal',
            'displayUpdateModal',
            'displaySummaryModal',
            'selectedVisit'
        ]);
    }    

    public function buildUIParams()
    {
        return [
            'title' => 'Site Visit List',
            'description' => 'View Site Visit List',
            'aside' => 'what is this',
        ];
    }

    public function getCaseSiteVisitService(): CaseSiteVisitService
    {
        return app(CaseSiteVisitService::class);
    }

    public function getSiteVisit()
    {
        $service = $this->getCaseSiteVisitService();
        if($this->filter){
            $service->filterResult($this->filter);
        }
        if($this->sortField){
            $service->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->customerId){
            return $service->getSiteVisitsForCustomer($this->customerId);
        }elseif($this->case){
            return $service->getSiteVisitsForCase($this->case->id);
        }else{
            return $service->getAllActiveSiteVisits();
        }

    }

    public function scheduleVisit()
    {
        $this->resetAllModals();
        $this->displayCreateModal = true;
    }

    public function manageVisit(int|string $visitId)
    {
        $visitId = (int) $visitId;
        $this->resetAllModals();
        $this->selectedVisit = TaskSiteVisit::find($visitId);
        $this->displayProfileModal = true;
    }    

    public function render()
    {
        $visits = $this->getSiteVisit();
        $siteVisits = $visits?->orderByDesc('created_at')->paginate(10);
        $viewSelectedCustomerVisit = $this->customerId !== null;

        return view('casemanagement::livewire.case.view-site-visit',[
            'ui_params' => $this->buildUIParams(),
            'siteVisits' => $siteVisits,
            'viewSelectedCustomerVisit' => $viewSelectedCustomerVisit,
        ]);
    }
}