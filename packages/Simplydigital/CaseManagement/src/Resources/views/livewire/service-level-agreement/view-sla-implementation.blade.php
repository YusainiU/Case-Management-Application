<div class="grid grid-cols-1 gap-4 p-4">
    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $ui_params['title'] }}"
            description="Service Level Management Application." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Apply SLA" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $slaps?->links() }}      
</div>