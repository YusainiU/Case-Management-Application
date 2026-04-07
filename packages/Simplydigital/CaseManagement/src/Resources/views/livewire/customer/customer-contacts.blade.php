<div class="grid grid-cols-1 gap-4 p-4">
    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="Customer Contacts" description="Manage your customer contacts here."
            aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />

    <x-casemanagement::form-elements.button-primary label="Create New Customer Contact" action="openCreateContactModal" />

    <x-casemanagement::form-elements.input-filter />
    
    {{ $customerContacts?->links() }}
    
</div>
