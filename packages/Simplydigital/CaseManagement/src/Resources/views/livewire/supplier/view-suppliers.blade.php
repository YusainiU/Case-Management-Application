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

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $title }}"
            description="Manage your Suppliers here." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Supplier" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $suppliers?->links() }}
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
            <tbody>
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
                                    <x-casemanagement::form-elements.button-secondary label="View"
                                        action="openProfileModal({{ $supplier->id }})" />
                                    <x-casemanagement::form-elements.button-secondary label="Edit"
                                        action="openUpdateModal({{ $supplier->id }})" />
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

</div>
