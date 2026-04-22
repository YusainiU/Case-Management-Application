<div class="grid grid-cols-1 gap-4 p-4">

    @if($displayCreateModal)
        <livewire:create-product />
    @endif

    @if($displayUpdateModal)
        <livewire:create-product :productId="$productId" />
    @endif

    @if($displayProfileModal)
        <livewire:product-profile :productId="$productId" />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $title }}"
            description="Manage your customers here." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Product" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $products?->links() }}
    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="name" direction="asc">
                Name
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="model" direction="asc">
                Model
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="type" direction="asc">
                Type
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="make" direction="asc">
                Make
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="version" direction="asc">
                Version
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Actions
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbody>
            @if ($products?->isNotEmpty())
                @foreach ($products as $product)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $products->firstItem() + $loop->index }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $product->name }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $product->model }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $product->type }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $product->make }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $product->version }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex items-center gap-2">
                                <x-casemanagement::form-elements.button-primary label="Edit" action="openUpdateModal({{ $product->id }})" />
                                <x-casemanagement::form-elements.button-secondary label="View" action="openProfileModal({{ $product->id }})" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <x-casemanagement::table-elements.table-td colspan="7">
                        <div class="flex flex-col items-center justify-center py-4">
                            <p class="mt-2 text-sm text-gray-500">No products found.</p>
                        </div>
                    </x-casemanagement::table-elements.table-td>
                </tr>
            @endif

        </tbody>

    </x-casemanagement::table-elements.table>


</div>
