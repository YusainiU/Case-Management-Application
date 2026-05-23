<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseSLAPService;
use Simplydigital\CaseManagement\Services\CaseAssetServices;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Services\UI;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;

class CreateCase extends ModalComponent
{
    public ?int $caseId = null;
    public ?tickets $case = null;
    public ?int $customerId = null;
    public array $ui = [];
    public array $form = [];
    public array $slapOptions = [];
    public array $assetOptions = [];
    public array $categoryOptions = [];
    public array $sourceOptions = [];
    public array $contactOptions = [];
    public array $customerOptions = [];
    public string $closureEvent = 'closeCreateCaseModal';
    public bool $displayForm = false;
    public ?Customer $customerSelected = null;

    protected $validationAttributes = [
        'form.sla_applications_id' => 'SLA',
        'form.customer_reference' => 'Customer Referennce',
        'form.category' => 'Case Category',
        'form.source' => 'Source',
        'form.list_of_assets' => 'Assets',
        'form.description' => 'Case Description',
        'form.short_description' => 'Case Title',
        'form.raised_by_user' => 'Customer Contact (User)',
        'form.raised_by_nonuser' => 'Customer Contact (Non-User)',        
        'form.contact_telephone' => 'Contact Telephone',
        'form.contact_email' => 'Contact Email',
    ];

    protected function rules()
    {
        return [
            'form.sla_applications_id' => 'required',
            'form.customer_reference' => 'nullable',
            'form.category' => 'required',
            'form.source' => 'required',
            'form.list_of_assets' => 'nullable',
            'form.description' => 'required',
            'form.short_description' => 'required|max:50',
            'form.raised_by_user' => 'nullable|int|required_without:form.raised_by_nonuser',
            'form.raised_by_nonuser' => 'nullable|string|required_without:form.raised_by_user',
            'form.contact_telephone' => 'required',
            'form.contact_email' => 'required|email',
        ];
    }

    public function mount()
    {
        $this->declareFormProperties();
        $this->displayForm = (bool) $this->customerId || (bool) $this->caseId;
        if($this->displayForm){
            $this->loadOptions();
            if($this->caseId) {
                $this->loadCase();
                $this->loadCaseForm();
            }
        }else{
            $this->loadCustomerOptions();
        }
        
        $this->ui = [
            'title' => 'Create Case',
            'confirmText' => 'Create',
            'widthsize' => '6xl',
            'cancelThisWith' => 'closeThisModal',
            'confirm' => 'save',
        ];        
    }

    public function loadCaseForm()
    {
        $this->form = [
            'sla_applications_id' => $this->case->sla_applications_id,
            'customer_reference' => $this->case->customer_reference,
            'category' => $this->case->category,
            'source' => $this->source,
            'list_of_assets' => $this->case->list_of_assets,
            'description' => $this->case->description,
            'short_description' => $this->case->short_description,
            'raised_by_user' => $this->case->raised_by_user,
            'raised_by_nonuser' => $this->case->raised_by_nonuser,
            'contact_telephone' => $this->case->contact_telephone,
            'contact_email' => $this->case->contact_email,
        ];
    }

    public function updatedFormRaisedByUser(int|string|null $userId)
    {
        $user = CustomerContacts::where('user_id', $userId)->first();
        if($user) {
            $this->form['contact_telephone'] = $user->customerUserDetails->phone;
            $this->form['contact_email'] = $user->customerUser->email;
        }
    }

    public function declareFormProperties()
    {
        $this->form = [
            'sla_applications_id' => null,
            'customer_reference' => null,
            'category' => null,
            'source' => null,
            'list_of_assets' => [],
            'description' => null,
            'short_description' => null,
            'raised_by_user' => null,
            'raised_by_nonuser' => null,
            'contact_telephone' => null,
            'contact_email' => null,
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

    protected function customerServices(): CaseCustomerServices
    {
        return app(CaseCustomerServices::class);
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
            'displayForm',
        ]);
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch($this->closureEvent);
    }

    public function loadCustomerOptions()
    {
        $service = $this->customerServices();
        //$this->customerOptions = $service->buildOptionsForCustomerSelect();
        $options = $service->buildOptionsForCustomerSelect();
        $this->customerOptions = UI::createArrayforMultiselect($options, 'id', 'name'); 
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
        $this->sourceOptions = CaseSource::options();

        //Options for Contacts
        $customerService = $this->customerServices();
        $this->contactOptions = $customerService->buildOptionsForCustomerContacts($this->customerId);
    }

    public function selectCustomer()
    {
        $id = (int) $this->customerId;
        $this->customerId = $id;
        $this->displayForm = true;
        $this->customerSelected = $this->customerServices()->getCustomer($id);
        $this->loadOptions();
    }

    public function save()
    {
        $this->validate();
        $this->form['customer_id'] = $this->customerId;
        $service = $this->ticketServices();
        $service->createNewTicket($this->form);
        $this->closeThisModal();        
    }

    public function render()
    {
        return view('casemanagement::livewire.case.create-case', [
            'ui_params' => $this->ui,
        ]);
    }


}