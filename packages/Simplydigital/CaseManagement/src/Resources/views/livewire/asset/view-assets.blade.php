<div class="grid grid-cols-1 gap-4 p-4">
    @if($displayCreateModal)
        <livewire:create-asset :customerId="$customerId"/>
    @endif

    @if($displayUpdateModal)
        <livewire:create-asset :assetId="$assetId" />
    @endif

    @if($displayProfileModal)
        <livewire:asset-profile :assetId="$assetId" />
    @endif
    
    @if($displayProductProfile)
        <livewire:product-profile :productId="$productId" />
    @endif

    @if($displayCustomerProfile)
        <livewire:customer-profile :customerId="$customerId" />
    @endif
    
    @if($displaySupplierProfile)
        <livewire:supplier-profile :supplierId="$supplierId" />
    @endif    

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $title }}"
            description="Manage your Customer Assets here." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Asset" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $assets?->links() }}
    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="Number" direction="asc">
                Asset
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="product_id" direction="asc">
                Product
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="customer_id" direction="asc">
                Customer
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Supplier
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Location
            </x-casemanagement::table-elements.table-th>            
        </thead>
        <tbody>
            @if ($assets?->isNotEmpty())
                @foreach ($assets as $asset)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $assets->firstItem() + $loop->index }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $asset->short_description }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <a href="#" wire:click.prevent="openProductProfileModal('{{ $asset->product_id }}')">
                                {{ $asset->product->name }}
                            </a>
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            @if($customerId)
                                {{ $asset->customer->name }}
                            @else                                
                                <a href="#" wire:click.prevent="openCustomerProfileModal('{{ $asset->customer_id }}')">
                                    {{ $asset->customer->name }}
                                </a>                                
                            @endif
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>                            
                            <a href="#" wire:click.prevent="openSupplierProfileModal('{{ $asset->supplier_id }}')">
                                {{ $asset->supplier->name }}
                            </a>
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $asset->location }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-4">
                                <x-casemanagement::form-elements.button-primary label="Edit" action="openUpdateModal({{ $asset->id }})" />
                                <x-casemanagement::form-elements.button-secondary label="View" action="openProfileModal({{ $asset->id }})" />                                
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <x-casemanagement::table-elements.table-td colspan="8" class="text-center">
                        No assets found.
                    </x-casemanagement::table-elements.table-td>
                </tr>
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>    
    {{ $assets?->links() }}

</div>