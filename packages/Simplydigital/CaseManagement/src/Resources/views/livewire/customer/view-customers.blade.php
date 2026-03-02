<div>

    @if($displayCreateModal)
        <livewire:create-customer />
    @endif

    <x-casemanagement::page-elements.section-title title="Customers" description="Manage your customers here."
        aside="what is this" />

    <x-casemanagement::page-elements.seperator />

    <x-casemanagement::form-elements.button-primary label="Create New Customer" action="openCreateModal" />

    <x-casemanagement::table-elements.table>

        <thead>
            <x-casemanagement::table-elements.table-th sortable field="name">
                Name
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="email">
                Email
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Actions
            </x-casemanagement::table-elements.table-th>
        </thead>

        <tbody>
            <x-casemanagement::table-elements.table-td>
                Support Span
            </x-casemanagement::table-elements.table-td>
            <x-casemanagement::table-elements.table-td>
                support@email.com
            </x-casemanagement::table-elements.table-td>
            <x-casemanagement::table-elements.table-td>
                <div class="flex space-x-2">
                    <x-casemanagement::form-elements.button-primary label="Edit" />
                    <x-casemanagement::form-elements.button-secondary label="Delete" />
                </div>
            </x-casemanagement::table-elements.table-td>
        </tbody>
    </x-casemanagement::table-elements.table>


</div>
