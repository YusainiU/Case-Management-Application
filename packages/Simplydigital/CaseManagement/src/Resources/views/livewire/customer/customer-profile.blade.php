<div class="grid grid-cols-1 gap-4 p-4">

    @if($showUpdateModal)
        <livewire:create-customer :customerId="$customerId" />
    @endif

    @if($showConfirmDelete)
        <livewire:delete-customer :customerId="$customerId" />
    @endif


    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="Customer Profile" description="{{ $customer->short_description }}"
            padding=" " aside="{{ $customer->active ? 'Account is Active' : 'Account is Not Active' }}" />        
    </div>

    <x-casemanagement::page-elements.seperator margin="2"/>

    <div class="flex flex-row gap-4 justify-items-start">
        <x-casemanagement::form-elements.button-primary label="Customers List" action="viewCustomerList" />
        <x-casemanagement::form-elements.button-primary label="Update Details" action="displayUpdate" />
        <x-casemanagement::form-elements.button-danger label="Delete" action="confirmDelete" />
    </div>

    <div class="grid grid-cols-2 gap-4">

        <div>
            <div class="mt-4">
                <x-casemanagement::page-elements.customer-info :customer="$customer" />                
            </div>
        </div>

        <div class="mt-4">
            <x-casemanagement::page-elements.customer-address :customer="$customer" :customerAddress="$customerAddress" />
        </div>

    </div>



    <x-casemanagement::page-elements.seperator margin="2" />

    <div class="my-4">
        <x-casemanagement::page-elements.tab :tabHeaders=$tabHeaders selectedTab="{{ $selectedTab }}">
            <x-slot name="tabSelectedComponent">
                Selected Tab
            </x-slot>
        </x-casemanagement::page-elements.tab>
        
        @if('contacts' === $selectedTab)
            <livewire:customer-contacts :customerId="$customerId" />   
        @endif
        @if('childs' === $selectedTab)
            <livewire:view-customer :viewChildCompanies="$customerId" />
        @endif
        @if('assets' === $selectedTab)
            <livewire:view-assets :customerId="$customerId" />
        @endif
        @if('sla' === $selectedTab)
            <livewire:view-sla-application :customerId="$customerId" />
        @endif
        @if('outstandingCases' === $selectedTab)
            <livewire:view-cases :customerId="$customerId" />
        @endif

    </div>

</div>
