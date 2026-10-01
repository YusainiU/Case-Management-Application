<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{

    protected $listeners = [
        'reloadCaseStatus' => '$refresh',
    ];

    public function mount()
    {
        $this->authorize('viewAny', tickets::class);
    }    
    private function ticketUtilityService(): CaseTicketUtilityServices
    {
        return new CaseTicketUtilityServices();
    }

    private function getTicketStatuses()
    {
        return $this->ticketUtilityService()->retrieveTicketStatuses();
    }

    private function getTotalOpenTickets()
    {
        return $this->ticketUtilityService()->getTotalOpenCases();
    }


    public function buildStatus()
    {
        $status = [];
        $cases = $this->getTicketStatuses();
        
        $status['unassigned'] = collect($cases)->sum('unassigned');
        $status['noUpdate'] = collect($cases)->sum('noUpdate');
        $status['totalOpenCases'] = $this->getTotalOpenTickets();
        $status['totalBreached'] = collect($cases)->sum('taskBreached');
        $status['nearBreach'] = collect($cases)->sum('closeToBreachWarning');
        $status['urgentLogs'] = collect($cases)->sum('numberOfUrgentLogs');
        $status['resolved'] = collect($cases)->sum('resolved');
        $status['currentlyWorking'] = collect($cases)->where('resolved', true)->count();
        $status['responded'] = collect($cases)->pluck('respondedAt')->whereNotNull()->count();
        $status['paused'] = collect($cases)->sum('paused');
        $status['totalVisits'] = collect($cases)->sum('totalVisits');
        $status['total'] = $cases['total'];

        return $status;
    }
    
    public function openBreached()
    {
        $this->openSelectedFilter('breached');
    }

    public function openNearBreach()
    {
        $this->openSelectedFilter('nearBreached');
    }

    public function openUnassigned()
    {
        $this->openSelectedFilter('unassigned');
    }

    public function openNoUpdate()
    {
        $this->openSelectedFilter('noUpdate');
    }

    public function openResolvedCases()
    {
        $this->openSelectedFilter(CaseState::Resolved->value);
    }

    public function openPausedCases()
    {
        $this->openSelectedFilter(CaseState::Paused->value);
    }

    public function openVisit()
    {
        $this->openSelectedFilter(CaseState::Site_Visit->value);
    }

    public function openAllOutstanding()
    {
        $this->openSelectedFilter(CaseState::Open->value);
    }

    public function openSelectedFilter(string $filter)
    {
        return $this->redirectRoute('casemanagement.cases.view', [
            'customerId' => null,
            'dashboardFilter' => $filter,
        ]);        
    }

    public function render()
    {
        $data = $this->buildStatus();
        return view('casemanagement::livewire.case.dashboard',[
            'status' => $data,
        ]); 
    }
}