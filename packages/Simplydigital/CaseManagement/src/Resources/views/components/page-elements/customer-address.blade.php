<x-casemanagement::page-elements.description-list-container>
    <x-casemanagement::page-elements.description-list-element label="Address" value="{{ $customerAddress }}" />
    <x-casemanagement::page-elements.description-list-element label="Telephone"
        value="{{ $customer->telephone_1 ?? 'Not available' }}" />
    <x-casemanagement::page-elements.description-list-element label="Portal Enabled"
        value="{{ $customer->portal_enabled ? 'Yes' : 'No' }}" />
</x-casemanagement::page-elements.description-list-container>
