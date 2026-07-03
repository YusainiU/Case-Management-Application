<div class="grid grid-cols-1 gap-4 p-4">

    @if($this->displayCreateModal)
        <livewire:create-site-visit :modal="true" :case="$case" />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title 
            title="{{ $ui_params['title'] }}"
            description="{{ $ui_params['description'] }}" 
            aside="what is this" 
        />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Schedule a Visit" action="scheduleVisit" />

    {{ $siteVisits?->links() }}

    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="created_at" direction="asc">
                Created At
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="customer_id" direction="asc">
                Site
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbody>

        </tbody>
    </x-casemanagement::table-elements.table>   

</div>