<div class="grid grid-cols-1 gap-4 p-4">
    @if ($displayCreateModal)
        <livewire:create-asset :customerId="$customerId" @close-create-modal="closeCreateModal" />
    @endif

    @if ($displayUpdateModal)
        <livewire:create-asset :assetId="$assetId" @close-update-modal="closeUpdateModal" />
    @endif

    @if ($displayProfileModal)
        <livewire:asset-profile :assetId="$assetId" @close-profile-modal="closeProfileModal" />
    @endif

    @if ($displayProductProfile)
        <livewire:product-profile :productId="$productId" @close-profile-modal="closeProfileModal" />
    @endif

    @if ($displayCustomerProfile)
        <livewire:customer-profile :customerId="$customerId" />
    @endif

    @if ($displaySupplierProfile)
        <livewire:supplier-profile :supplierId="$supplierId" @close-profile-modal="closeProfileModal" />
    @endif

    <x-casemanagement::page-elements.page-header-section section="Customer Asset Management" title="{{ $title }}"
        description="Manage your Customer Assets here." :showCreateButton="true" createButtonLabel="Create New Asset"
        createAction="openCreateModal" showBackButton="true" />

    <section class="overflow-hidden rounded-2xl shadow-sm">


        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($assets)
                <div class="text-sm text-slate-500">
                    @if ($assets->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $assets->firstItem() }}–{{ $assets->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $assets->total() }}
                        </span>
                        Assets
                    @else
                        No asset found
                    @endif
                </div>
            @endif
        </div>
        
       <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($assets?->hasPages())
                <div class="text-slate-100!">
                    {{ $assets->links('livewire::tailwind') }}
                </div>
            @endif
        </div>        

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
            <tbody class="divide-y divide-slate-100 bg-white">
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
                                <a href="#"
                                    wire:click.prevent="openProductProfileModal('{{ $asset->product_id }}')">
                                    {{ $asset->product->name }}
                                </a>
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                @if ($customerId)
                                    {{ $asset->customer->name }}
                                @else
                                    <a href="#"
                                        wire:click.prevent="openCustomerProfileModal('{{ $asset->customer_id }}')">
                                        {{ $asset->customer->name }}
                                    </a>
                                @endif
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                <a href="#"
                                    wire:click.prevent="openSupplierProfileModal('{{ $asset->supplier_id }}')">
                                    {{ $asset->supplier->name }}
                                </a>
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $asset->location }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                <div class="flex flex-row gap-4">
                                    <x-casemanagement::form-elements.button-edit action="openUpdateModal({{ $asset->id }})" label="Edit" />
                                    <x-casemanagement::form-elements.button-view action="openProfileModal({{ $asset->id }})" label="View" />
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

    </section>

</div>
