<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
class ManageCase extends ModalComponent
{
    public ?tickets $case = null;
    public array $tabHeaders = [
        'sla' => 'SLA',
        'update' => 'Update',
        'progress' => 'Progress',
        'visit' => 'Visit',
    ];
    public string $selectedTab = 'sla';

     /**
     * @var array<string, string>
     */
    protected $listeners = [
        'closeManageSlaModal' => 'closeManageSlaModal',
        'reloadCaseStatus' => '$refresh',
    ];

    public function resetTabs()
    {
        $this->reset([
            'selectedTab',
        ]);
    }

    public function closeManageSlaModal()
    {
        $this->resetTabs();
    }

    public function selectTab(string $tabKey)
    {
        if (array_key_exists($tabKey, $this->tabHeaders)) {
            $this->selectedTab = $tabKey;
        }
    }

    public function ticketService()
    {
        return app(CaseTicketServices::class);
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');
    }

    public function buildUI()
    {
        $caseNumber = $this->case->number ?? null;
        $title = $caseNumber ? "Manage Case - {$caseNumber}" : "Manage Case";
        return [
            'title' => $title,
            'description' => $this->case->customer()->first()->name ?? null,
        ];
    }
    public function render()
    {
        $ui = $this->buildUI();
        return view('casemanagement::livewire.case.manage-case',[
            'ui' => $ui,
        ]);
    }
}