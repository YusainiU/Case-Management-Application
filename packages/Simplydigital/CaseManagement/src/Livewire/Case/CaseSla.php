<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Models\tickets;

class CaseSla extends ModalComponent
{
    public ?tickets $case = null;
    
    private function buildUI()
    {
        return [
            'title' => 'SLA Status',
        ];
    }

    public function render()
    {
        $ui = $this->buildUI();
        return view('casemanagement::livewire.case.case-sla',[
            'ui' => $ui,
        ]);
    }

}