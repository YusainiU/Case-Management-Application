<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Validation\Rules\Enum;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;
use Simplydigital\CaseManagement\Services\CaseServiceLevelAgreement;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\CaseManagement\Enums\DaysOfWeek;
use Simplydigital\CaseManagement\Enums\slaTypes;
use Illuminate\Validation\ValidationException;


class CreateServiceLevelAgreement extends ModalComponent
{

    public ?int $slaId = null;    
    public serviceLevelAgreements $sla;
    public array $ui = [];
    public array $form = [];
    public array $daysOfWeek = [];
    public array $types = [];
    public string $closureEvent = 'closeCreateSLAModal';


    public function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.short_description' => 'required|string|max:255',
            'form.start_day' => ['required', new Enum(DaysOfWeek::class)],
            'form.end_day' => ['required', new Enum(DaysOfWeek::class)],
            'form.service_start_time' => 'required|date_format:H:i',
            'form.service_end_time' => 'required|date_format:H:i',
            'form.include_public_holiday' => 'boolean',
            'form.cover_details' => 'required|string|max:255',
            'form.active' => 'boolean',
            'form.response_time' => 'required|integer|between:0,99',
            'form.fixed_time' => 'required|integer|between:0,99',
            'form.type' => ['required', new Enum(slaTypes::class)],
        ];
    }    
    
    protected $validationAttributes = [
        'form.name' => 'Name',
        'form.short_description' => 'Short Description',
        'form.start_day' => 'Start Day',
        'form.end_day' => 'End Day',
        'form.service_start_time' => 'Start Time',
        'form.service_end_time' => 'End Time',
        'form.include_public_holiday' => 'Include Public Holiday',
        'form.cover_details' => 'Cover Details',
        'form.active' => 'Active',
        'form.response_time' => 'Response Time',
        'form.fixed_time' => 'Fixed Time',
        'form.type' => 'Type',
    ];
    
    public function mount()
    {
        
        $this->ui = [
            'title' => 'Create Service Level Agreement (SLA)',
            'confirmText' => 'Create',
            'widthsize' => '6xl',
            'cancelThisWith' => 'closeThisModal',
            'confirm' => 'save',
        ];

        $this->declareFormProperties();

        if ($this->slaId) {
            $this->loadSLA();
            $this->authorize('update', $this->sla);
        }else{
            $this->authorize('create', serviceLevelAgreements::class);
        }

        $this->loadOptions();
    }

    protected function slaService(): CaseServiceLevelAgreement
    {
        return app(CaseServiceLevelAgreement::class);
    }
    
    protected function utilityService(): CaseManagementUtilityServices
    {
        return app(CaseManagementUtilityServices::class);
    }

    public function declareFormProperties()
    {
        $this->form = [
            'name' => null,
            'short_description' => null,
            'start_day' => null,
            'end_day' => null,
            'service_start_time' => null,
            'service_end_time' => null,
            'include_public_holiday' => false,
            'cover_details' => null,
            'active' => true,
            'response_time' => 0,
            'fixed_time' => 0,
            'type' => null,
        ];
    }

    public function loadSLA()
    {
        $service = $this->slaService();
        $this->sla = $service->getSLAById($this->slaId);
        $this->ui['title'] = 'Update SLA';
        $this->ui['confirmText'] = 'Update';
        $this->closureEvent = 'closeUpdateSLAModal';
        $this->loadSLAForm();
    }

    public function loadSLAForm()
    {
        $service = $this->utilityService();
        $startTime = $this->sla->service_start_time ? $service->secondsToHMS($this->sla->service_start_time,'HM') : '00:00';
        $endTime = $this->sla->service_end_time ? $service->secondsToHMS($this->sla->service_end_time,'HM') : '00:00';
        $this->form = [
            'name' => $this->sla->name,
            'short_description' => $this->sla->short_description,
            'start_day' => $this->sla->start_day,
            'end_day' => $this->sla->end_day,
            'service_start_time' => $startTime,
            'service_end_time' => $endTime,
            'include_public_holiday' => $this->sla->include_public_holiday,
            'cover_details' => $this->sla->cover_details,
            'active' => $this->sla->active,
            'response_time' => $this->sla->getResponseTime(),
            'fixed_time' => $this->sla->getFixedTime() ,
            'type' => $this->sla->type,
        ];
    }

    public function loadOptions()
    {

        $this->daysOfWeek = DaysOfWeek::options();
        $this->types = slaTypes::options();

    }

    public function save()
    {
        $service = $this->utilityService();
        $slaService = $this->slaService();
        $this->validate();
        // try {
        //     $this->validate();
        // } catch(ValidationException $e) {
        //     dd($e->validator->errors());
        // }
        $this->form['service_start_time']=$service->HMSToSeconds($this->form['service_start_time']);
        $this->form['service_end_time']=$service->HMSToSeconds($this->form['service_end_time']);        
        if($this->slaId) {
            $this->authorize('update', $this->sla);
            $slaService->updateSLA($this->sla, $this->form);
        }else{
            $this->authorize('create', serviceLevelAgreements::class);
            $slaService->createSLA($this->form);
        }
        $this->closeThisModal();
    }

    public function resetAll()
    {
        $this->reset([
            'form',
        ]);
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch($this->closureEvent);
    }    

    public function render()
    {
        return view('casemanagement::livewire.service-level-agreement.create-service-level-agreement', [
            'ui_params' => $this->ui,
        ]);        
    }
    
}