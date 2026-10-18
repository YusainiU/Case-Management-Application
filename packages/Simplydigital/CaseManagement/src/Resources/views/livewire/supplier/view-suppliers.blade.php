<div class="grid grid-cols-1 gap-4 p-4">

    @if ($displayCreateModal)
        <livewire:create-supplier />
    @endif

    @if ($displayProfileModal)
        <livewire:supplier-profile :supplierId="$supplierId" />
    @endif

    @if ($displayUpdateModal)
        <livewire:create-supplier :supplierId="$supplierId" />
    @endif

    <x-casemanagement::page-elements.page-header-section section="Supplier Management" title="{{ $title }}"
        description="Manage your suppliers here." :showCreateButton="true" createButtonLabel="Create New Supplier"
        createAction="openCreateModal" showBackButton="true" />


    <section class="">

        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($suppliers)
                <div class="text-sm text-slate-500">
                    @if ($suppliers->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $suppliers->total() }}
                        </span>
                        Supplier
                    @else
                        No supplier found
                    @endif
                </div>
            @endif
        </div>        

        {{-- Filters and pagination --}}
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($suppliers?->hasPages())
                <div class="text-slate-100!">
                    {{ $suppliers->links('livewire::tailwind') }}
                </div>
            @endif
        </div>

        <div class="my-4">
            <x-casemanagement::table-elements.table>
                <thead>
                    <x-casemanagement::table-elements.table-th>
                        &nbsp;
                    </x-casemanagement::table-elements.table-th>
                    <x-casemanagement::table-elements.table-th sortable field="name" direction="asc">
                        Name
                    </x-casemanagement::table-elements.table-th>
                    <x-casemanagement::table-elements.table-th sortable field="email" direction="asc">
                        Email
                    </x-casemanagement::table-elements.table-th>
                    <x-casemanagement::table-elements.table-th sortable field="telephone" direction="asc">
                        Phone
                    </x-casemanagement::table-elements.table-th>
                    <x-casemanagement::table-elements.table-th>
                        Actions
                    </x-casemanagement::table-elements.table-th>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @if ($suppliers?->isNotEmpty())
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <x-casemanagement::table-elements.table-td>
                                    {{ $suppliers->firstItem() + $loop->index }}
                                </x-casemanagement::table-elements.table-td>
                                <x-casemanagement::table-elements.table-td>
                                    {{ $supplier->name }}
                                </x-casemanagement::table-elements.table-td>
                                <x-casemanagement::table-elements.table-td>
                                    {{ $supplier->email }}
                                </x-casemanagement::table-elements.table-td>
                                <x-casemanagement::table-elements.table-td>
                                    {{ $supplier->telephone }}
                                </x-casemanagement::table-elements.table-td>
                                <x-casemanagement::table-elements.table-td>
                                    <div class="flex items-center gap-2">
                                        <x-casemanagement::form-elements.button-edit action="openProfileModal({{ $supplier->id }})" label="View" />
                                        <x-casemanagement::form-elements.button-view label="Edit" action="openUpdateModal({{ $supplier->id }})" />
                                    </div>
                                </x-casemanagement::table-elements.table-td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <x-casemanagement::table-elements.table-td colspan="5" class="text-center">
                                No suppliers found.
                            </x-casemanagement::table-elements.table-td>
                        </tr>
                    @endif
                </tbody>
            </x-casemanagement::table-elements.table>
        </div>

    </section>

</div>
