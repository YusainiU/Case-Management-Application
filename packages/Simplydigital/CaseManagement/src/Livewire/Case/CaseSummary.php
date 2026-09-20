<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Models\tickets;

class CaseSummary extends ModalComponent
{    

    public string $title = 'Case Summary';
    public int $caseId;
    public ?tickets $case = null;
    public array $caseItems = [];

    private function caseService(): CaseTicketServices
    {
        return app(CaseTicketServices::class);
    }

    public function mount()
    {
        $this->authorize('viewAny', Tickets::class);
        $this->loadCase();
    }

    public function loadCase()
    {
        $service = $this->caseService();
        $case = $service->getCaseById($this->caseId);
        $this->case = $case;
        $this->caseItems = $service->buildSummaryItems($case);
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');   
    }

    public function render()
    {
        return view('casemanagement::livewire.case.case-summary');
    }



}