<div class="grid grid-cols-1 gap-4 p-4">

    @if($showCreateContactModal)
        <livewire:create-update-customer-contact />
    @endif

    @if($showContactProfileModal)
        <livewire:userprofile-component :userId="$userProfileId" />
    @endif

    @if($showContactUpdateModal)
        <livewire:update-customer-contact :customerContactId="$customerContactId" />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="Customer Contacts" description="Manage your customer contacts here."
            aside="what is this" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />

    <x-casemanagement::form-elements.button-primary label="Create New Customer Contact" action="openCreateContactModal" />

    <x-casemanagement::form-elements.input-filter />
    
    {{ $customerContacts?->links() }}

    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Name
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Customer
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Email
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Contact Role
            </x-casemanagement::table-elements.table-th>            
            <x-casemanagement::table-elements.table-th>
                Phone
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Has Portal Access
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbody>
            @if($customerContacts?->isNotEmpty())

                @foreach($customerContacts as $contact)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{  $customerContacts->firstItem() + $loop->index }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <a href="#" wire:click.prevent="openContactProfileModal({{ $contact->customerUser?->id }})" class="text-blue-600 hover:underline">                            
                            {{ $contact->customerUser->name }}
                            </a>
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $contact->customer?->name }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $contact->customerUser?->email }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $contact->customerRole->label() ?? 'N/A' }}
                        </x-casemanagement::table-elements.table-td>                        
                        <x-casemanagement::table-elements.table-td>
                            {{ $contact->customerUserDetails?->phone }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $contact->has_portal_access ? 'Yes' : 'No' }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <x-casemanagement::form-elements.button-secondary label="Update Contact" action="openUpdateContactModal({{ $contact->id }})" />
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach

            @else
                <tr>
                    <x-casemanagement::table-elements.table-td colspan="7" class="text-center">
                        No customer contacts found.
                    </x-casemanagement::table-elements.table-td>
                </tr>
            @endif  
        </tbody>        
    </x-casemanagement::table-elements.table>
    
</div>
