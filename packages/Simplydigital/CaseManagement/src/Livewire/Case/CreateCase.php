<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseSLAPService;
use Simplydigital\CaseManagement\Services\CaseAssetServices;
use Simplydigital\CaseManagement\Services\UI;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;


class CreateCase extends ModalComponent
{
    public ?int $caseId = null;
    public ?tickets $case = null;
    public array $ui = [];
    public array $form = [];
    public array $slapOptions = [];
    public array $assetOptions = [];
    public array $categoryOptions = [];
    public array $sourceOtions = [];
    public string $closureEvent = 'closeCreateCaseModal';

    protected $validationAttributes = [
        'form.number' => 'Number',
        'form.customer_id' => 'Customer',
        'form.sla_applications_id' => 'SLA',
        'form.customer_reference' => 'Customer Referennce',
        'form.category' => 'Case Category',
        'form.list_of_assets' => 'Assets',
        'form.description' => 'Case Description',
        'form.customer_contact_user' => 'Customer Contact (User)',
        'form.customer_contact_nonuser' => 'Customer Contact (Non-User)',        
        'form.contact_telephone' => 'Contact Telephone',
        'form.contact_email' => 'Contact Email',
    ];

    protected function rules()
    {
        return [
            'form.customer_id' => 'required',
            'form.sla_applications_id' => 'required',
            'form.customer_reference' => 'nullable',
            'form.category' => 'required',
            'form.list_of_assets' => 'nullable',
            'form.description' => 'required',
            'form.customer_contact_user' => 'nullable|int|required_without:form.customer_noncontact',
            'form.customer_noncontact' => 'nullable|string|required_without:form.customer_contact',
            'form.contact_telephone' => 'required',
            'form.contact_email' => 'required|email',
        ];
    }

    public function loadCaseForm()
    {
        $this->form = [
            'customer_id' => $this->case->customer_id,
            'sla_applications_id' => $this->case->sla_application_id,
            'customer_reference' => $this->case->customer_reference,
            'category' => $this->case->category,
            'list_of_assets' => $this->case->list_of_assets,
            'description' => $this->case->description,
            'customer_contact' => $this->case->customer_contact,
            'contact_telephone' => $this->case->contact_telephone,
            'contact_email' => $this->case->contact_email,
        ];
    }
    
    protected function ticketServices(): CaseTicketServices
    {
        return app(CaseTicketServices::class);
    }

    protected function slapServices(): CaseSLAPService
    {
        return app(CaseSLAPService::class);
    }

    protected function assetServices(): CaseAssetServices
    {
        return app(CaseAssetServices::class);
    }

    public function loadCase()
    {
        $service = $this->ticketServices();
        $this->case = $service->getCaseById($this->caseId);
        $this->ui['title'] = 'Update Case';
        $this->ui['confirmText'] = 'Update';
        $this->closureEvent = 'closeUpdateCaseModal';
        $this->loadCaseForm();       

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
    
    public function loadOptions()
    {
        //SlAP Options
        $slapService = $this->slapServices();
        $this->slapOptions = $slapService->buildOptionsForSLAPSelect($this->customerId);

        //Asset Options
        $assetService = $this->assetServices();
        $assetOptions = $assetService->buildOptionsForAssetSelect($this->customerId);
        $this->assetOptions = UI::createArrayforMultiselect($assetOptions);

        //Category && Source
        $this->categoryOptions = CaseCategory::options();
        $this->sourceOtions = CaseSource::options();
    }

    public function save()
    {
        
    }

    public function render()
    {
        return view('casemanagement::livewire.case.create-case', [
            'ui_params' => $this->ui,
        ]);
    }


}