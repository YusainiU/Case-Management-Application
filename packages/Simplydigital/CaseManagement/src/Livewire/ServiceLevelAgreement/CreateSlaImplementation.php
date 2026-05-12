<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Services\CaseServiceLevelAgreement;
use Simplydigital\CaseManagement\Services\CaseSLAPService;
use Simplydigital\CaseManagement\Enums\slaAppPriorities;

class CreateSlaImplementation extends ModalComponent
{
    public ?int $slapId = null;
    public ?slaApplication $slap = null;
    public ?int $customerId = null;
    public array $ui = [];
    public array $form = [];
    public array $customers = [];
    public array $slas = [];
    public array $priorities = [];
    public string $closureEvent = 'closeCreateSLAPModal';

    protected $validationAttributes = [
        'form.name' => 'Name',
        'form.short_description' => 'Short Description',
        'form.priority' => 'Priority',
        'form.customer_id' => 'Customer',
        'form.service_level_agreement_id' => 'SLA',
        'form.active' => 'Active',
    ];

    public function mount()
    {
        $this->ui = [
            'title' => 'Implement SLA',
            'confirmText' => 'Create',
            'widthsize' => '6xl',
            'cancelThisWith' => 'closeThisModal',
            'confirm' => 'save',
        ];

        $this->declareFormProperties();

        if ($this->slapId) {
            $this->loadSLAP();
        }

        $this->loadOptions();
    }    

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.short_description' => 'required|string|max:255',
            'form.priority' => 'required',
            'form.customer_id' => 'required',
            'form.service_level_agreement_id' => 'required|int',
            'form.active' => 'boolean',
        ];
    }

    public function declareFormProperties()
    {
        $this->form = [
            'name' => null,
            'short_description' => null,
            'priority' => null,
            'customer_id' => $this->customerId ?? null,
            'service_level_agreement_id' => null,
            'active' => true,
        ];
    }    

    protected function slaService(): CaseServiceLevelAgreement
    {
        return app(CaseServiceLevelAgreement::class);
    }

    protected function slapService(): CaseSLAPService
    {
        return app(CaseSLAPService::class);
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

    public function loadSLAP()
    {
        $service = $this->slapService();
        $this->slap = $service->getSLAPById($this->slapId);
        $this->ui['title'] = 'Update Implementation';
        $this->ui['confirmText'] = 'Update';
        $this->closureEvent = 'closeUpdateSLAPModal';
        $this->loadSLAPForm();        
    }

    public function loadSLAPForm()
    {
        $this->form = [
            'name' => $this->slap->name,
            'short_description' => $this->slap->short_description,
            'priority' => $this->slap->priority,
            'customer_id' => $this->slap->customer_id,
            'service_level_agreement_id' => $this->slap->service_level_agreement_id,
            'active' => $this->slap->active,
        ];
    }

    public function loadOptions()
    {
        $this->customers = app()->make(CaseCustomerServices::class)->buildOptionsForCustomerSelect();
        $this->slas = $this->slaService()->buildOptionsForSLASelect();
        $this->priorities = slaAppPriorities::options();
        $mult = $this->priorities;        
    }

    public function save()
    {
        // try {         
        //     $this->validate();
        // }catch(ValidationException $e){
        //     $this->message = $e->validator->errors();
        // }

        $this->validate();

        $service = $this->slapService();
        if ($this->slapId) {
            $service->updateSLAP($this->slap, $this->form);
        } else {
            $service->createSLAP($this->form);
        }
        $this->dispatch($this->closureEvent);
    }        

    public function render()
    {
        return view('casemanagement::livewire.service-level-agreement.create-sla-implementation',[
            'ui_params' => $this->ui,
        ]);
    }
}
