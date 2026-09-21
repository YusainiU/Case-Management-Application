<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Simplydigital\CaseManagement\Services\UI;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Models\CustomerContacts;

class CreateCustomerContact extends ModalComponent
{
 
    public int|string|null $customerId = null;
    public int|string|null $customerContactId = null;
    public ?string $contact = null;
    public array $customers = [];
    public int|string|null $customer = null;
    public array  $contactRoles = [];
    public ?string $contactRole;
    public bool $isUpdate = false;
    public string $title = 'Create Customer Contact';
    public string $confirmText = 'Create';
    public ?string $name = null;
    public ?string $email = null;
    public ?string $address_1 = null;
    public ?string $address_2 = null;
    public ?string $county = null;
    public ?string $postcode = null;
    public string $country='United Kingdom';
    public ?string $phone = null;
    public ?string $description = null;
    public bool $active = true;
    public bool $hasPortalAccess = false;

    public function customerService(): CaseCustomerServices
    {
        return app(CaseCustomerServices::class);
    }

    public function mount()
    {

        $this->authorize('viewAny', CustomerContacts::class);        
        $this->customers = $this->customerService()->buildOptionsForCustomerSelect();
        if(\sizeof($this->customers)){
            $this->customers = UI::createArrayforMultiselect($this->customers,'id','name');
        }
        $this->contactRoles = CustomerContactRoles::options();
        if ($this->customerContactId) {
            $this->getCustomerContact();
            $this->isUpdate = true;
            $this->title = 'Update Customer Contact';
            $this->confirmText = 'Update';
        }
        if($this->customerId) {
            $this->customer = $this->customerId;
        }
    }
    public function resetAll()
    {
        $this->customerContactId = null;
        $this->contact = null;
        $this->isUpdate = false;
    }

    public function getCustomerContact()
    {
        $service = app('case-customer-services');
        $this->contact = $service->getCustomerContactDetails($this->customerContactId);
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch('closeCreateCustomerContactModal');
    }

    public function getAllActiveCustomers()
    {
        $service = app('case-customer-services');
        return $service->getAllActiveCustomers_id_name();
    }    

    public function rules()
    {
        return [
            'customer' => 'required',
            'contactRole' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'address_1' => 'required',
            'address_2' => 'nullable',
            'county' => 'nullable',
            'postcode' => 'required',
            'country' => 'nullable',
            'phone' => 'nullable',
            'description' => 'nullable',
        ];
    }

    public function save()
    {
        $this->validate();
        $service = app('case-customer-services');
        $input = [
            'customer_id' => $this->customer,
            'name' => $this->name,
            'email' => $this->email,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'county' => $this->county,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'phone' => $this->phone,
            'description' => $this->description,
            'active' => $this->active,
            'has_portal_access' => $this->hasPortalAccess,
            'contact_role' => $this->contactRole,
        ];
        if ($this->isUpdate) {
            $this->authorize('update', CustomerContacts::find($this->customerContactId));
            $service->updateCustomerContact($this->customerContactId, $input);                        
        } else {
            $this->authorize('create', CustomerContacts::class);
            $service->createCustomerContact($input);            
        }
        $this->resetAll();
        $this->dispatch('closeCreateCustomerContactModal');

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer-contact');
    }
}