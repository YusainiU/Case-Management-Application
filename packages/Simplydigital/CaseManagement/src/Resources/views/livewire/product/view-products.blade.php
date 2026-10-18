<div class="grid grid-cols-1 gap-4 p-4">

    @if ($displayCreateModal)
        <livewire:create-product />
    @endif

    @if ($displayUpdateModal)
        <livewire:create-product :productId="$productId" />
    @endif

    @if ($displayProfileModal)
        <livewire:product-profile :productId="$productId" />
    @endif

    <x-casemanagement::page-elements.page-header-section section="Customer Management" title="{{ $title }}"
        description="Manage your products here." :showCreateButton="true" createButtonLabel="Create New Product"
        createAction="openCreateModal" showBackButton="true" />

    <section class="">

        {{-- Directory header --}}
        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($products)
                <div class="text-sm text-slate-500">
                    @if ($products->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $products->firstItem() }}–{{ $products->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $products->total() }}
                        </span>
                        Products
                    @else
                        No products found
                    @endif
                </div>
            @endif
        </div>

        {{-- Filters and pagination --}}
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($products?->hasPages())
                <div class="text-slate-100!">
                    {{ $products->links('livewire::tailwind') }}
                </div>
            @endif
        </div>

        <div class="overflow-hidden ">
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
                <tbody class="divide-y divide-slate-100 bg-white">
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
                                        <x-casemanagement::form-elements.button-edit action="openUpdateModal({{ $product->id }})" label="Edit" />
                                        <x-casemanagement::form-elements.button-view action="openProfileModal({{ $product->id }})" label="View" />
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
    </section>


</div>
