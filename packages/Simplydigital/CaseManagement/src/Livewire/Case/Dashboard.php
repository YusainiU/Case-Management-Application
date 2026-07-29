<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
class Dashboard extends Component
{

    protected $listeners = [
        'reloadCaseStatus' => '$refresh',
    ];
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
        $status['resolved'] = collect($cases)->sum('resolved');
        $status['currentlyWorking'] = collect($cases)->where('resolved', true)->count();
        $status['responded'] = collect($cases)->pluck('respondedAt')->whereNotNull()->count();
        $status['paused'] = collect($cases)->sum('paused');
        $status['totalVisits'] = collect($cases)->sum('totalVisits');
        $status['total'] = $cases['total'];

        return $status;
    }    
    public function render()
    {
        $data = $this->buildStatus();
        return view('casemanagement::livewire.case.dashboard',[
            'status' => $data,
        ]); 
    }
}