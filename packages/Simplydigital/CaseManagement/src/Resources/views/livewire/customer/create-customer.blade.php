<div>
    <x-casemanagement::page-elements.modal title="{{ $title }}" widthsize="6xl" cancelThisWith="closeThisModal"
        confirmText="Create" confirm="save">

        <div class="flex flex-col gap-2 w-full">
            <div class="flex flex-row gap-8">
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Name" :required=true />
                    <x-casemanagement::form-elements.input-text modal="name" placeholder="Customer Name"
                        :required=true />
                </div>
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Primary Type" :required=true />
                    <x-casemanagement::form-elements.select model="primaryType" name="Type"
                        placeholder="Customer Primary Type" :options=$primaryTypes />
                </div>
                @if ($primaryType === Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes::Child_Company->value && !empty($customers))
                    <div class="flex flex-col gap-2">
                        <x-casemanagement::form-elements.input-label name="Parent Company" :required=true />
                        <x-casemanagement::form-elements.searchable-dropdown :options=$customers :property=$parentCompany />
                    </div>
                @endif
            </div>

            @if ($primaryType)
                <div class="flex flex-row gap-8">
                    <div class="flex flex-col gap-2">
                        <x-casemanagement::form-elements.input-label name="Secondary Type" :required=true />
                        <x-casemanagement::form-elements.select model="childType" name="Type"
                            placeholder="Customer Secondary Type" :options=$childTypes />
                    </div>
                </div>
            @endif

            <div class="flex flex-row gap-8">
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Address 1" :required=true />
                    <x-casemanagement::form-elements.input-text modal="address_1" placeholder="Address 1"
                        :required=true />
                </div>
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Address 2" />
                    <x-casemanagement::form-elements.input-text modal="address_2" placeholder="Address 2"
                        :required=true />
                </div>
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Address 3" />
                    <x-casemanagement::form-elements.input-text modal="address_3" placeholder="Address 3"
                        :required=true />
                </div>
            </div>
            <div class="flex flex-row gap-8">
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Telephone" :required=true />
                    <x-casemanagement::form-elements.input-text modal="telephone_1" placeholder="Address 1"
                        :required=true />
                </div>
            </div>
        </div>

    </x-casemanagement::page-elements.modal>
</div>
