<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseUserServices;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Livewire\Attributes\On;

class ManageCaseSLA extends ModalComponent
{
    public ?tickets $case = null;
    public array $forms = [];
    public array $internals = [];
    public $isPaused = false;
    public ?string $message = null;
    public bool $noSlaTask = false;
    public bool $isTodayWorkingDay = true;
    public bool $isNowWorkingHours = true;

    protected $validationAttributes = [
        'forms.responded_at' => 'Responded At',
        'forms.fixed_at' => 'Fixed At',
        'forms.assigned_to' => 'Assigned To',
        'forms.log' => 'Progress Note',
    ];

    protected function rules()
    {
        return [
            'forms.assigned_to' => 'required|exists:users,id|required_with:forms.responded_at,forms.fixed_at',
            'forms.responded_at' => 'nullable|date|required_with:forms.completed_at',
            'forms.fixed_at' => 'nullable|date',            
            'forms.log' => 'nullable|required_with:forms.fixed_at,forms.responded_at',
        ];
    }

    public function mount()
    {
        $this->authorize('viewAny', tickets::class);
        if(!$this->case->slaTasks){
            $this->noSlaTask = true;
        }
        $this->callData();
        $services = $this->ticketService();
        $this->isTodayWorkingDay = $services->isTodayBusinessWorkingDay($this->case);
        $this->isNowWorkingHours = $services->isNowInsideServiceHours($this->case);
    }

    public function callData()
    {
        $this->case?->refresh();
        $this->case?->load('slaTasks.activePause');
        $this->loadSLAData();
        $this->loadSelections();
        $this->checkPause();        
    }

    public function loadSLAData()
    {
        $this->forms = [
            'responded_at' => $this->case->slaTasks?->responded_at ? $this->case->slaTasks->responded_at->format('Y-m-d\TH:i') : null,
            'fixed_at' => $this->case->slaTasks?->fixed_at ? $this->case->slaTasks->fixed_at->format('Y-m-d\TH:i') : null,
            'assigned_to' => (string) $this->case?->assigned_to,
            'log' => null,
        ];
    }

    public function loadSelections()
    {
        $this->internals = CaseUserServices::getAllEngineers();
    }

    public function checkPause()
    {
        $this->isPaused = $this->case->slaTasks?->paused;
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
        $this->authorize('update', $this->case);
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
        $this->authorize('update', $this->case);
        $this->ticketService()->unassignedUser($this->case);
        $this->callData();
        $this->message = "Case has been unassigned";
        $this->dispatch('reloadCaseStatus');
    }

    #[On('activities-changed')]
    public function createSlaTask()
    {
        $this->noSlaTask = false;
        $this->ticketService()->createSlaTask($this->case);
        $this->dispatch('reloadCaseStatus');
        $this->dispatch('$refresh');
    }

    public function pause()
    {
        $this->authorize('update', $this->case);
        $result = $this->ticketService()->pauseSla($this->case);
        $this->callData();
        $this->dispatch('reloadCaseStatus');
        $this->message = $result ? 'SLA has been paused' : 'SLA could not be paused; check the current SLA status';        
    }

    public function resume()
    {
        $this->authorize('update', $this->case);
        $result = $this->ticketService()->resumeSla($this->case);
        $this->callData();
        $this->dispatch('reloadCaseStatus');
        $this->message = $result ? 'SLA has been resumed' : 'SLA could not be resumed; check the current SLA status';        
    }

    public function cancelPause()
    {
        $this->authorize('update', $this->case);
        $result = $this->ticketService()->cancelPauseSla($this->case);
        $this->callData();
        $this->dispatch('reloadCaseStatus');
        $this->message = $result ? 'SLA pause has been cancelled' : 'SLA pause could not be cancelled; check the current SLA status';        
    }

    public function render()
    {
        return view('casemanagement::livewire.case.manage-case-sla');
    }
}
