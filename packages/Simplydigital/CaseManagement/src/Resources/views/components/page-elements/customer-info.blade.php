<x-casemanagement::page-elements.description-list-container>
    <x-casemanagement::page-elements.description-list-element label="Ref #" value="{{ $customer->number }}" />

    <x-casemanagement::page-elements.description-list-element label="Name" value="{{ $customer->number }}"
        value="{{ $customer->name }}" />
    <x-casemanagement::page-elements.description-list-element label="Primary Type" value="{{ $customer->number }}"
        value="{{ $customer->primary_type->label() }}" />
    <x-casemanagement::page-elements.description-list-element label="Secondary Type" value="{{ $customer->number }}"
        value="{{ $customer->child_type }}" />
    @if ($customer->primary_type->value === Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes::Child_Company->value)
        <x-casemanagement::page-elements.description-list-element label="Parent Company" navigateEnabled=true
            navigateToComponent="parentCompany" value="{{ $customer->parentCompany->name }}" />
    @endif
</x-casemanagement::page-elements.description-list-container>
