<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
class CaseStatus extends Component
{
    public ?int $caseId = null;

    public function ticketUtilityService(): CaseTicketUtilityServices
    {
        return new CaseTicketUtilityServices();
    }

    public function getTicketStatuses()
    {
        $status = $this->ticketUtilityService()->retrieveTicketStatuses();
    }
    
    public function render()
    {
        return view('casemanagement::livewire.case.case-status');
    }
}