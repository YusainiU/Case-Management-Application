<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use App\Models\User;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Simplydigital\CaseManagement\Services\UI;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\CaseManagement\Services\CaseUserServices;

class AddExistingUserAsContact extends ModalComponent
{
 
    public int|string|null $customerId = null;
    public array $customers = [];
    public int|string|null $user_id = null;
    public int|string|null $customer = null;
    public array  $contactRoles = [];
    public array $allCustomerContacts = [];
    public ?string $contactRole;
    public string $title = 'Add Existing User As Contact';
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

    public function UserService(): CaseUserServices
    {
        return app(CaseUserServices::class);
    }

    public function mount()
    {

        $this->authorize('viewAny', CustomerContacts::class);        
        $this->customers = $this->customerService()->buildOptionsForCustomerSelect();
        $this->getAllCustomerContacts();
        if(\sizeof($this->customers)){
            $this->customers = UI::createArrayforMultiselect($this->customers,'id','name');
        }
        $this->contactRoles = CustomerContactRoles::options();
        if($this->customerId) {
            $this->customer = $this->customerId;
        }        

    }

    public function closeThisModal()
    {
        $this->dispatch('close-add-modal');
    }

    public function getAllActiveCustomers()
    {
        $service = app('case-customer-services');
        return $service->getAllActiveCustomers_id_name();
    }

    public function getAllCustomerContacts()
    {
        $service = $this->UserService();
        $customers = $service->getAllCustomerContacts();
        $this->allCustomerContacts = UI::createArrayforMultiselect($customers,'id','name');
    }

    public function rules()
    {
        return [
            'customer' => 'required',
            'contactRole' => 'required',
            'user_id' => 'required',
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
        $contact = User::find($this->user_id);
        $input = [
            'customer_id' => $this->customer,
            'user_id' => $this->user_id,
            'email' => $contact->email,
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
        $this->authorize('create', CustomerContacts::class);
        $service->addExistingUserAsCustomerContact($input);
        $this->closeThisModal();       
    }
    public function render()
    {
        return view('casemanagement::livewire.customer.add-existing-user-as-contact');
    }
}