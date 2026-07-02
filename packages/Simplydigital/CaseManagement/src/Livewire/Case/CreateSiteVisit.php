<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\UI;

class CreateSiteVisit extends ModalComponent
{

    public ?taskSiteVisit $taskSiteVisit = null;
    public ?tickets $case = null;
    public ?int $caseId = null;
    public bool $updateMode = false;
    public bool $modalMode = true;
    public array $caseOptions = [];
    public bool $displayForm = false;

    protected $validationAttributes = [
        'form.ticket_id' => 'Case Description',
        'form.assigned_to' => 'Assigned To',
        'form.visit_scheduled_at' => 'Visit Scheduled At',
        'form.first_visit' => 'First Visit',
        'form.scheduled_by' => 'Scheduled By',
        'form.description' => 'Visit Notes',
        'form.enroute_at' => 'Enroute At',
        'form.onsite_at' => 'Onsite At',
        'form.offsite_at' => 'Offsite At',
        'form.active' => 'Active Visit',
    ];

    protected function rules()
    {
        return [
            'form.assigned_to' => 'nullable',
            'form.visit_scheduled_at' => 'required|date',
            'form.first_visit' => 'nullable|boolean',
            'form.scheduled_by' => 'nullable',
            'form.description' => 'required|string',
            'form.enroute_at' => 'nullable|datetime',
            'form.onsite_at' => 'nullable|datetime',
            'form.offsite_at' => 'nullable|datetime',
            'form.active' => 'nullable|boolean',
            'form.ticket_id' => 'required|exists:tickets,id',
        ];
    }

    public function resetAll()
    {
        $this->reset([
            'updateMode',
            'modalMode',
            'displayForm',
        ]);
    }
    
    public function declareFormProperties()
    {
        return [
            'form.assigned_to' => $this->taskSiteVisit?->assigned_to ?? null,
            'form.visit_scheduled_at' => $this->taskSiteVisit?->visit_scheduled_at ?? null,
            'form.first_visit' => $this->taskSiteVisit?->first_visit ?? false,
            'form.scheduled_by' => $this->taskSiteVisit?->scheduled_by ?? null,
            'form.description' => $this->taskSiteVisit?->description ?? '',
            'form.enroute_at' => $this->taskSiteVisit?->enroute_at ?? null,
            'form.onsite_at' => $this->taskSiteVisit?->onsite_at ?? null,
            'form.offsite_at' => $this->taskSiteVisit?->offsite_at ?? null,
            'form.active' => $this->taskSiteVisit?->active ?? true,
            'form.ticket_id' => $this->taskSiteVisit?->ticket_id ?? null,
        ];
    }

    public function initialize()
    {
        $this->updateMode = $this->taskSiteVisit !== null;
        $this->case = $this->taskSiteVisit?->ticket ?? null;
        $this->buildOptions();
        $this->declareFormProperties();
    }

    public function buildUI()
    {
        return  [
            'title' => $this->updateMode ? 'Update Site Visit' : 'Create Site Visit',
            'submitButtonText' => $this->updateMode ? 'Update' : 'Create',
            'description' => $this->updateMode ? 'Update the details of the site visit.' : 'Fill in the details to create a new site visit.',
            'widthsize' => '6xl',
            'cancelThisWith' => 'closeThisModal',
            'confirmText' => $this->updateMode ? 'update' : 'save',
            'confirm' => 'save',
        ];
    }

    public function buildOptions()
    {
        //Get All active cases
        $cOpts = tickets::whereNull('closed_datetime')->pluck('short_description', 'id')->toArray();
        if($cOpts){
            $this->caseOptions = UI::createArrayforMultiselect($cOpts, 'id', 'name');
        }
        
    }

    public function selectCase()
    {
        if(!$this->caseId){
            return;
        }
        $this->case = tickets::find($this->caseId);
        $this->form['ticket_id'] = $this->caseId;
        $this->displayForm = true;
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch('closeCreateVisitModal');
    }

    public function render()
    {
        $this->initialize();
        return view('casemanagement::livewire.case.create-site-visit',[
            'ui_params' => $this->buildUI(),
            'form' => $this->declareFormProperties(),
            'case' => $this->case,
            'update' => $this->updateMode,
            'modal' => $this->modalMode,
        ]);
    }
}