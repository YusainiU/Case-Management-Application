<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
class ViewSlaPause extends Component
{

    public ?tickets $case = null;

    public function mount()
    {
        $this->authorize('viewAny', $this->case);
    }

    private function getPauses()
    {
        $service = app(CaseTicketServices::class);        
        $pauses = $service->getAllCaseSlaPauses($this->case);
        return $pauses;
        
    }

    private function ui()
    {
        return [
            'title' => 'Pauses to SLA',
        ];
    }

    public function render()
    {
        $pauses = null;
        if($this->case){
            $pauses = $this->getPauses();
        }

        return view('casemanagement::livewire.case.view-sla-pause',[
            'pauses' => $pauses,
            'ui_params' => $this->ui(),    
        ]);
    }


}