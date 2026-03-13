<div class="grid grid-cols-1 gap-4 p-4">

    @if ($displayCreateModal)
        <livewire:create-customer />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="Customers" description="Manage your customers here."
            aside="what is this" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />

    <x-casemanagement::form-elements.button-primary label="Create New Customer" action="openCreateModal" />

    <x-casemanagement::table-elements.table>

        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="name">
                Name
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="primary_type">
                Primary Type
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Parent Company
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Portal Enabled
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Action
            </x-casemanagement::table-elements.table-th>
        </thead>

        <tbody>
            @if($customers?->isNotEmpty())

                @foreach($customers as $customer)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{  $loop->iteration }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $customer->name }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $customer->primary_type->label() }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $customer->parent_company ? $customer->parentCompany->name : null }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $customer->portal_enabled ? 'Yes' : 'No' }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex space-x-2">
                                <x-casemanagement::form-elements.button-primary label="Edit" />
                                <x-casemanagement::form-elements.button-secondary label="Delete" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr> 
                @endforeach
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>


</div>
