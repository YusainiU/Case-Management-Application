@php
    
    use Simplydigital\CaseManagement\Services\UI;

@endphp

<div class="grid grid-cols-1 gap-4 p-4">

    @if($displayCreateModal)
        <livewire:create-case />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $ui_params['title'] }}"
            description="Cases Management: List" aside="what is this" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Case" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />

     {{ $cases?->links() }}

</div>