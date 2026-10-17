<?php

namespace Simplydigital\CaseManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\UserManagement\Services\UserManagementServices;

class CaseCustomerServices
{
    private ?string $sortBy = null;

    private string $sortDirection = 'asc';

    private string|int|null $filter = null;

    private ?int $viewChildCompanies = null;

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
    }

    public function filterResult(?string $filter)
    {
        $this->filter = $filter;
    }

    public function selectCildCompanies(int $parentId)
    {
        $this->viewChildCompanies = $parentId;
    }

    public function buildOptionsForCustomerSelect()
    {
        $options = Customer::where('active', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();

        return $options;
    }

    public function buildOptionsForCustomerContacts(int $customerId)
    {
        $options = CustomerContacts::with('customerUser')
            ->where('customer_id', $customerId)
            ->get()
            ->mapWithKeys(fn ($contact) => [
                $contact->user_id => $contact->customerUser->name,
            ])
            ->toArray();

        return $options;
    }

    public function buildProfileItems(Customer $customer)
    {
        $items = [];
        $utility = app()->make(CaseManagementUtilityServices::class);
        foreach ($customer->getAttributes() as $key => $value) {

            if ($utility::checkIfDate($value)) {
                $value = $utility::displayDateValue($value);
            }
            if ($key == 'primary_type' && $value) {
                $value = $customer->primary_type->label();
            }
            if ($key == 'child_type' && $value) {
                $value = $customer->child_type->label();
            }
            if ($key == 'parent_company' && $value) {
                $value = $customer->parentCompany->name;
            }
            if ($key == 'active' || $key == 'portal_enabled') {
                $value = $value ? 'Yes' : 'No';
            }

            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }

        return $items;
    }

    public function getAllActiveCustomers()
    {
        $query = Customer::query();
        $query->where('active', true);
        if ($this->viewChildCompanies) {
            $query->where('parent_company', $this->viewChildCompanies);
        }

        if ($this->filter) {
            $filterValue = '%'.$this->filter.'%';
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

    public function getCustomer(int $customerId)
    {
        return Customer::find($customerId);
    }

    public function getCustomerContactById(int $contactId)
    {
        return CustomerContacts::find($contactId);
    }

    private function sortThis(Builder $query)
    {
        $sortOptions = [
            'user_id' => [
                'table' => 'users',
                'foreign_key' => 'customer_contacts.user_id',
                'column' => 'users.name',
            ],
            'customer_id' => [
                'table' => 'customers',
                'foreign_key' => 'customer_contacts.customer_id',
                'column' => 'customers.name',
            ],
        ];

        if ($this->sortBy) {
            if (isset($sortOptions[$this->sortBy])) {
                $sort = $sortOptions[$this->sortBy];

                $query->leftJoin(
                    $sort['table'],
                    $sort['foreign_key'],
                    '=',
                    $sort['table'].'.id'
                )
                    ->orderBy($sort['column'], $this->sortDirection)
                    ->select('customer_contacts.*');
            } else {
                $query->orderBy(
                    'customer_contacts.'.$this->sortBy,
                    $this->sortDirection
                );
            }

            return $query;
        }
    }

    public function getAllCustomerContacts()
    {
        $query = CustomerContacts::query();
        if ($this->filter) {
            $filterValue = '%'.$this->filter.'%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('customerRole', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue)
                    ->orWhereHas('customerUserDetails', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue)
                            ->orWhere('email', 'like', $filterValue)
                            ->orWhere('phone', 'like', $filterValue);
                    })
                    ->orWhereHas('customer', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }
        if ($this->sortBy) {
            $query = $this->sortThis($query);
        }

        return $query;
    }

    public function getCustomerContacts(int $customerId)
    {
        $query = CustomerContacts::query();
        $query->where('customer_id', $customerId);
        if ($this->filter) {
            $filterValue = '%'.$this->filter.'%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('customerRole', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue)
                    ->orWhereHas('customerUserDetails', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue)
                            ->orWhere('email', 'like', $filterValue)
                            ->orWhere('phone', 'like', $filterValue);
                    })
                    ->orWhereHas('customer', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }

        if ($this->sortBy) {
            $this->sortThis($query);
        }

        return $query;
    }

    public function getCustomerContactDetails(int $contactId)
    {
        return CustomerContacts::find($contactId);
    }

    public function createCustomerContact(array $input)
    {

        $userService = new UserManagementServices;

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

        $contact = new CustomerContacts;
        $contact->customer_id = $input['customer_id'];
        $contact->description = $input['description'];
        $contact->active = $input['active'] ?? true;
        $contact->customerRole = $input['contact_role'];
        $contact->has_portal_access = $input['has_portal_access'] ?? false;
        $contact->user_id = $user->id;
        $contact->save();

        return $contact;
    }

    public function updateCustomerContactOnly(int $contactId, array $input)
    {
        /** @var CustomerContacts $contact */
        $contact = CustomerContacts::find($contactId);
        if (! $contact) {
            return null;
        }

        $contact->customer_id = $input['customer_id'];
        $contact->description = $input['description'];
        $contact->active = $input['active'];
        $contact->customerRole = $input['customerRole'];
        $contact->has_portal_access = $input['has_portal_access'];
        $contact->save();

        return $contact;
    }

    public function updateCustomerContact(int $contactId, array $input)
    {
        /** @var CustomerContacts $contact */
        $contact = CustomerContacts::find($contactId);
        if (! $contact) {
            return null;
        }

        $userService = new UserManagementServices;
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
