<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseSiteVisitService;
use Simplydigital\CaseManagement\Services\CaseUserServices;
use Simplydigital\CaseManagement\Services\UI;

class CreateSiteVisit extends ModalComponent
{
    public ?TaskSiteVisit $taskSiteVisit = null;

    public ?tickets $case = null;

    public ?int $caseId = null;

    public bool $updateMode = false;

    public bool $modalMode = true;

    public array $caseOptions = [];

    public array $userOptions = [];

    public bool $displayForm = false;
    
    public ?string $message = null;

    public bool $completed = false;

    public array $form = [];

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
        'form.shor_description' => 'Short Description',
    ];

    protected function rules()
    {
        return [
            'form.assigned_to' => 'nullable',
            'form.visit_scheduled_at' => 'required|date',
            'form.first_visit' => 'nullable|boolean',
            'form.scheduled_by' => 'nullable',
            'form.short_description' => 'required|string',
            'form.description' => 'required|string',
            'form.enroute_at' => 'nullable',
            'form.onsite_at' => 'nullable',
            'form.offsite_at' => 'nullable',
            'form.active' => 'nullable|boolean',
        ];
    }

    public function mount()
    {
        $this->checkCaseExists();
        $this->initialize();
    }

    public function visitService(): CaseSiteVisitService
    {
        return app(CaseSiteVisitService::class);
    }

    public function resetAll()
    {
        $this->reset([
            'updateMode',
            'modalMode',
            'displayForm',
            'message',
        ]);
    }

    public function declareFormProperties()
    {
        $this->form = [
            'assigned_to' => $this->taskSiteVisit?->assigned_to ?? null,
            'visit_scheduled_at' => $this->taskSiteVisit?->visit_scheduled_at?->format('Y-m-d\TH:i') ?? null,
            'first_visit' => $this->taskSiteVisit?->first_visit ?? false,
            'scheduled_by' => $this->taskSiteVisit?->scheduled_by ?? null,
            'description' => $this->taskSiteVisit?->description ?? '',
            'enroute_at' => $this->taskSiteVisit?->enroute_at?->format('Y-m-d\TH:i') ?? null,
            'onsite_at' => $this->taskSiteVisit?->onsite_at?->format('Y-m-d\TH:i') ?? null,
            'offsite_at' => $this->taskSiteVisit?->offsite_at?->format('Y-m-d\TH:i') ?? null,
            'active' => $this->taskSiteVisit?->active ?? true,
            'ticket_id' => $this->taskSiteVisit?->ticket_id ?? null,
            'short_description' => $this->taskSiteVisit?->short_description ?? null,
        ];
    }

    public function initialize()
    {

        if ($this->caseId) {
            $this->case = tickets::find($this->caseId);
            $this->displayForm = true;
        }
        $this->case ??= $this->taskSiteVisit?->ticket;
        $this->updateMode = $this->taskSiteVisit !== null;
        $this->buildOptions();
        $this->declareFormProperties();
        $this->form['ticket_id'] ??= $this->case?->id;
        if($this->updateMode) {
            $this->completed = $this->taskSiteVisit->offsite_at !== null;
        }
    }

    public function checkCaseExists()
    {
        if ($this->case) {
            $this->caseId = $this->case->id;
        }

    }

    public function save()
    {
        if (! $this->case) {
            return;
        }
        $this->validate();
        $service = $this->visitService();
        $data = [
            'ticket_id' => $this->case->id,
            'assigned_to' => $this->form['assigned_to'],
            'short_description' => $this->form['short_description'],
            'description' => $this->form['description'],
            'visit_scheduled_at' => $this->form['visit_scheduled_at'],
            'active' => true,
        ];
        if ($this->updateMode && $this->taskSiteVisit) {
            $updated = $service->updateVisit($this->taskSiteVisit, $data, $this->form);
            $this->message = $updated 
                        ? "Visit details have been updated"
                        : null;
        } else {
            $created = $service->createVisit($this->case, $data);
            if ($created) {
                $this->taskSiteVisit = $created;
                $this->message ??= 'Visit schedule has been created';
            }
        }
        $this->declareFormProperties();
        $this->taskSiteVisit->refresh();
        $this->case->refresh();
        $this->closeThisModal();
    }

    public function completeVisit()
    {
        $service = $this->visitService();
        $canComplete = $service->canComplete($this->taskSiteVisit);
        if($canComplete){
            $completed = $service->completeVisit($this->taskSiteVisit);
            if($completed){
                $this->message = "The visit has been completed";
            }
            $this->closeThisModal();
        }else{
            $this->message = "Requires Enroute, Onsite and Offsite datetimes";
        }
        $this->case->refresh();
        $this->taskSiteVisit->refresh();
        
    }

    public function removeVisit()
    {
        //Cancel and Remove
        $visitNumber = $this->taskSiteVisit->number;
        $removed = $this->visitService()->cancelAndRemoveVisit($this->taskSiteVisit);
        if($removed){
            $this->message = "Visit $visitNumber has been removed";
        }
        $this->case->refresh();
        $this->taskSiteVisit->refresh();
        $this->closeThisModal();
        
    }

    public function resetToastMessage()
    {
        $this->reset([
            'message',
        ]);
    }    

    public function buildUI()
    {
        $maxWidth = $this->case ? '4xl' : '6xl';

        return [
            'title' => $this->updateMode ? 'Update Site Visit' : 'Create Site Visit',
            'submitButtonText' => $this->updateMode ? 'Update' : 'Create',
            'description' => $this->updateMode ? 'Update the details of the site visit.' : 'Fill in the details to create a new site visit.',
            'widthsize' => $maxWidth,
            'cancelThisWith' => 'closeThisModal',
            'confirmText' => $this->updateMode ? 'update' : 'save',
            'confirm' => 'save',
        ];
    }

    public function buildOptions()
    {
        // Get All active cases
        $cOpts = tickets::whereNull('closed_datetime')->pluck('short_description', 'id')->toArray();
        if ($cOpts) {
            $this->caseOptions = UI::createArrayforMultiselect($cOpts, 'id', 'name');
        }
        // Get All active internal users
        $this->userOptions = CaseUserServices::getAllInternalUserIds();

    }

    public function selectCase()
    {
        if (! $this->caseId) {
            return;
        }
        $this->initialize();
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch('closeCreateVisitModal');
    }

    public function render()
    {
        return view('casemanagement::livewire.case.create-site-visit', [
            'ui_params' => $this->buildUI(),
            'update' => $this->updateMode,
            'modal' => $this->modalMode,
        ]);
    }
}
