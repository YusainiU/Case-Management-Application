<div>

    <x-casemanagement::page-elements.modal 
        title="{{ $title }} - {{ $customerContact->customerUser?->name }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal"
        confirmText="{{ $confirmText }}" 
        confirm="save"
    >
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Customer" :required=true />
                <x-casemanagement::form-elements.searchable-select
                    name="Customer"
                    :required="true"
                    :options="$customers"
                    wireModel="customerId"
                />                 
                <x-casemanagement::form-elements.input-error field="customerId" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Contact Role" :required=true />
                <x-casemanagement::form-elements.select model="contactRole" name="contactRole" placeholder="Contact Role" :options=$contactRoles />
                <x-casemanagement::form-elements.input-error field="contactRole" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Description" :required="false" />
                <x-casemanagement::form-elements.textarea name="description" model="description"
                    hint="Contact Notes" />
                <x-casemanagement::form-elements.input-error field="description" />
            </div>            

            <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" :value=$active model="active" label="Active"/>
                <x-casemanagement::form-elements.checkbox name="Has Portal Access" :value=$hasPortalAccess model="hasPortalAccess" label="Has Portal Access"/>
            </div>              

        </div>
    </x-casemanagement::page-elements.modal>

</div>