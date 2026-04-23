<div class="grid grid-cols-1 gap-4 p-4">
    
    @if($displayCreateModal)
        <livewire:create-supplier />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $title }}" description="Manage your Suppliers here." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Supplier" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $suppliers?->links() }}

</div>