<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\UserManagement\Services\UserManagementServices;
class CaseCustomerServices
{

    private $sortBy;
    private $sortDirection = 'asc';
    private $filter;


    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection;
        }
    }

    public function filterResult($filter)
    {
        $this->filter = $filter;
    }

    public function getAllActiveCustomers()
    {
        $query = Customer::query();
        $query->where('active', true);

        if ($this->filter) {
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('name', 'like', $filterValue)
                    ->orWhere('primary_type', 'like', $filterValue)
                    ->orWhere('portal_enabled', 'like', $filterValue)
                    ->orWhere('child_type', 'like', $filterValue)
                    ->orWhereHas('parentCompany', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query;
    }

    public function getAllActiveCustomers_id_name()
    {
        $c = Customer::select('id', 'name')
            ->where('active', true)
            ->get()
            ->sortBy('name')
            ->toArray();
        return array_values($c);
    }

    public function getCustomer($customerId)
    {
        return Customer::find($customerId);
    }

    public function getAllCustomerContacts()
    {
        $query = CustomerContacts::query();
        if($this->filter){
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('customerRole', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue)
                    ->orWhereHas('userDetails', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query;
    }

    public function getCustomerContacts($customerId)
    {
        $query = CustomerContacts::where('customer_id', $customerId);
        if($this->filter){
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('customerRole', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue)
                    ->orWhereHas('userDetails', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query;
    }

    public function getCustomerContactDetails($contactId)
    {
        return CustomerContacts::find($contactId);
    }

    public function createCustomerContact($input)
    {

        $userService = new UserManagementServices();
        
        $password = $userService->generateRandomPassword();
        $userInput = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $password,
        ];
        $user = $userService->createUserOnly($userInput);

        $userDetailsInput = [
            'user_id' => $user->id,
            'address_1' => $input['address_1'] ?? 'Unavailable',
            'address_2' => $input['address_2'] ?? 'Unavailable',        
            'county' => $input['county'] ?? 'Unavailable',
            'postcode' => $input['postcode'] ?? 'Unavailable',
            'country' => $input['country'] ?? 'United Kingdom',
            'phone' => $input['phone'] ?? 'Unavailable',
            'identification' => 'customer',
        ];        
        $userService->createUserDetails($userDetailsInput);

        $roles = ['Customer'];
        $user->syncRoles($roles);

        $contact = new CustomerContacts();
        $contact->customer_id = $input['customer_id'];
        $contact->description = $input['description'];
        $contact->active = $input['active'] ?? true;
        $contact->customerRole = $input['contact_role'];
        $contact->has_portal_access = $input['has_portal_access'] ?? false;
        $contact->user_id = $user->id;
        $contact->save();
        return $contact;
    }
    
    public function updateCustomerContact($contactId, $input)
    {
        $contact = CustomerContacts::find($contactId);
        if (!$contact) {
            return null;
        }

        $userService = new UserManagementServices();
        $user = $contact->userDetails;
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->save();

        $userDetails = $user->userDetails;
        $userDetails->address_1 = $input['address_1'] ?? 'Unavailable';
        $userDetails->address_2 = $input['address_2'] ?? 'Unavailable';        
        $userDetails->county = $input['county'] ?? 'Unavailable';
        $userDetails->postcode = $input['postcode'] ?? 'Unavailable';
        $userDetails->country = $input['country'] ?? 'United Kingdom';
        $userDetails->phone = $input['phone'] ?? 'Unavailable';
        $userDetails->save();

        $contact->description = $input['description'];
        $contact->active = $input['active'];
        $contact->customerRole = $input['contact_role'];
        $contact->has_portal_access = $input['has_portal_access'];
        $contact->save();

        return $contact;
    }   
    
}
