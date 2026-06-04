<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseUserServices;
use Simplydigital\CaseManagement\Services\CaseTicketServices;

class ManageCaseSLA extends ModalComponent
{
    public ?tickets $case = null;
    public array $forms = [];
    public array $internals = [];
    public $isPaused = false;
    public ?string $message = null;

    protected $validationAttributes = [
        'forms.responded_at' => 'Responded At',
        'forms.fixed_at' => 'Fixed At',
        'forms.assigned_to' => 'Assigned To',
        'forms.completed_at' => 'Completed At',
        'forms.log' => 'Progress Note',
    ];

    protected function rules()
    {
        return [
            'forms.assigned_to' => 'required|exists:users,id|required_with:forms.responded_at,forms.completed_at,forms.fixed_at',
            'forms.responded_at' => 'nullable|date|required_with:forms.completed_at',
            'forms.fixed_at' => 'nullable|date',            
            'forms.completed_at' => 'nullable|date',
            'forms.log' => 'nullable|required_with:forms.completed_at,forms.fixed_at,forms.responded_at',
        ];
    }

    public function mount()
    {
        $this->callData();
    }

    public function callData()
    {
        $this->loadSLAData();
        $this->loadSelections();
        $this->checkPause();        
    }

    public function loadSLAData()
    {
        $this->forms = [
            'responded_at' => $this->case->slaTasks->responded_at ? $this->case->slaTasks->responded_at->format('Y-m-d\TH:i') : null,
            'fixed_at' => $this->case->slaTasks->fixed_at ? $this->case->slaTasks->fixed_at->format('Y-m-d\TH:i') : null,
            'assigned_to' => (string) $this->case->assigned_to,
            'completed_at' => $this->case->slaTasks->completed_at ? $this->case->slaTasks->completed_at->format('Y-m-d\TH:i') : null,
            'log' => null,
        ];
    }

    public function loadSelections()
    {
        $this->internals = CaseUserServices::getAllInternalUserIds();
    }

    public function checkPause()
    {
        $this->isPaused = $this->case->slaTasks->paused;
    }

    public function ticketService():CaseTicketServices
    {
        return new CaseTicketServices();
    }

    public function resetToastMessage()
    {
        $this->reset([
            'message',
        ]);
    }

    public function updateSla()
    {
        $this->validate();
        $result = $this->ticketService()->updateCaseSLADetails($this->case, $this->forms);
        $this->callData();
        $this->message = $result 
                ? "These have been updated: " . implode(", ", $result)
                : "Nothing was updated";
        
        $this->dispatch('reloadCaseStatus');
        

    }

    public function unassign()
    {
        $this->ticketService()->unassignedUser($this->case);
        $this->callData();
        $this->message = "Case has been unassigned";
        $this->dispatch('reloadCaseStatus');
    }

    public function render()
    {
        return view('casemanagement::livewire.case.manage-case-sla');
    }
}
