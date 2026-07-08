<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;

class CaseStatus extends Component
{

    protected $listeners = [
        'reloadCaseStatus' => '$refresh',
    ];
    public function buildStatus()
    {
        $status = [];
        $cases = $this->getTicketStatuses();
        /*
        Example ...
            "numberOfUrgentLogs" => 0
            "lastUrgentLogCreatedAt" => null
            "lastCustomerLogCreatedAt" => null
            "totalVisits" => 0
            "totalVisited" => 0
            "currentlyEnroute" => 0
            "currentlyOnsite" => 0
            "visitScheduled" => false
            "respondBy" => "26-05-2026 09:42"
            "fixBy" => "26-05-2026 11:42"
            "respondedAt" => null
            "fixedAt" => null
            "taskBreached" => 1
            "breachType" => ""
            "closeToBreachWarning" => 0
            "resolved" => null
            "currentlyWorking" => false
            "closed" => false
        */
        
        $status['unassigned'] = collect($cases)->sum('unassigned');
        $status['noUpdate'] = collect($cases)->sum('noUpdate');
        $status['totalOpenCases'] = $this->getTotalOpenTickets();
        $status['totalBreached'] = collect($cases)->sum('taskBreached');
        $status['nearBreach'] = collect($cases)->sum('closeToBreachWarning');
        $status['urgentLogs'] = collect($cases)->sum('numberOfUrgentLogs');
        $status['resolved'] = collect($cases)->pluck('resolved')->whereNotNull()->count();
        $status['currentlyWorking'] = collect($cases)->where('resolved', true)->count();
        $status['responded'] = collect($cases)->pluck('respondedAt')->whereNotNull()->count();
        $status['paused'] = collect($cases)->sum('paused');
        $status['totalVisits'] = collect($cases)->sum('totalVisits');

        return $status;
    }

    public function ticketUtilityService(): CaseTicketUtilityServices
    {
        return new CaseTicketUtilityServices();
    }

    public function getTicketStatuses()
    {
        return $this->ticketUtilityService()->retrieveTicketStatuses();
    }

    public function getTotalOpenTickets()
    {
        return $this->ticketUtilityService()->getTotalOpenCases();
    }

    public function allOpenCases()
    {
        $this->dispatch('allOpenCases');
    }

    public function allBreachedCases()
    {
        $this->dispatch('allBreachedCases');
    }

    public function allNearBreachCases()
    {
        $this->dispatch('allBreachedCases');
    }

    public function allResolvedCases()
    {
        $this->dispatch('allResolvedCases');
    }

    public function allPausedCases()
    {
        $this->dispatch('AllPausedCases');
    }
   
    public function render()
    {
        $data = $this->buildStatus();
        return view('casemanagement::livewire.case.case-status',[
            'data' => $data,
        ]);
    }
}