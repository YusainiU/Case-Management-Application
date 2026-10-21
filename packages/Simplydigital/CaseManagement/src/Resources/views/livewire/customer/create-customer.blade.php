<div>
    <x-casemanagement::page-elements.modal title="{{ $title }}" widthsize="6xl" cancelThisWith="closeThisModal"
        confirmText="{{ $confirmText }}" confirm="save">

        {{-- md:grid-cols-[150px_1fr] --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text model="name" placeholder="Customer Name" :required=true />
                <x-casemanagement::form-elements.input-error field="name" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Primary Type" :required=true />
                <x-casemanagement::form-elements.select model="primaryType" name="Type"
                    placeholder="Customer Primary Type" :options=$primaryTypes />
                <x-casemanagement::form-elements.input-error field="primaryType" />
            </div>

            @if (
                $primaryType === Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes::Child_Company->value &&
                    !empty($customers))
                <div>
                    <x-casemanagement::form-elements.input-label name="Parent Company" :required=true />
                    {{-- <x-casemanagement::form-elements.searchable-dropdown :options=$customers property="parentCompany" /> --}}
                    <x-casemanagement::form-elements.searchable-select
                        name="Parent Customer"
                        :required="true"
                        :options="$customers"
                        wireModel="customerId"
                    />                    
                </div>
            @endif

            @if ($primaryType)
                <div>
                    <x-casemanagement::form-elements.input-label name="Secondary Type" :required=true />
                    <x-casemanagement::form-elements.select model="childType" name="Type"
                        placeholder="Customer Secondary Type" :options=$childTypes />
                </div>
            @endif

            <div>
                <x-casemanagement::form-elements.input-label name="Address 1" :required=true />
                <x-casemanagement::form-elements.input-text model="address_1" placeholder="Address 1" :required=true />
                <x-casemanagement::form-elements.input-error field="address_1" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Address 2" />
                <x-casemanagement::form-elements.input-text model="address_2" placeholder="Address 2" :required=false />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Address 3" />
                <x-casemanagement::form-elements.input-text model="address_3" placeholder="Address 3" :required=false />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Telephone" :required=false />
                <x-casemanagement::form-elements.input-text model="telephone_1" placeholder="Telephone"
                    :required=false />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="URL" :required=false />
                <x-casemanagement::form-elements.input-text model="url" placeholder="Company Website URL"
                    :required=false />
                <x-casemanagement::form-elements.input-error field="url" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="description" :required="false" />
                <x-casemanagement::form-elements.textarea name="description" model="description"
                    hint="Customer Description" />
                <x-casemanagement::form-elements.input-error field="description" />
            </div>
            <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" :value=$active model="active" label="Active"/>
                <x-casemanagement::form-elements.checkbox name="Portal Enabled" :value=$portalEnabled model="portalEnabled" label="Portal Enabled"/>
            </div>

        </div>

    </x-casemanagement::page-elements.modal>
</div>
