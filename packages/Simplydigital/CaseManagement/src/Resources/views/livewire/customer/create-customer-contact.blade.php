<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $title }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal"
        confirmText="{{ $confirmText }}" 
        confirm="save"
    >
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Customer" :required=true />
                <x-casemanagement::form-elements.searchable-dropdown :options=$customers property="customer" />
                <x-casemanagement::form-elements.input-error field="customer" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Contact Role" :required=true />
                <x-casemanagement::form-elements.select model="contactRole" name="contactRole" placeholder="Contact Role" :options=$contactRoles />
                <x-casemanagement::form-elements.input-error field="contactRole" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text model="name" placeholder="Contact Name" :required=true />
                <x-casemanagement::form-elements.input-error field="name" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Email" :required=true />
                <x-casemanagement::form-elements.input-text model="email" placeholder="Contact Email" :required=true />
                <x-casemanagement::form-elements.input-error field="email" />                
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Address 1" :required=true />
                <x-casemanagement::form-elements.input-text model="address_1" placeholder="Address Line 1" :required=true />
                <x-casemanagement::form-elements.input-error field="address_1" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Address 2" :required=false />
                <x-casemanagement::form-elements.input-text model="address_2" placeholder="Address Line 2" :required=false />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="County" :required=false />
                <x-casemanagement::form-elements.input-text model="county" placeholder="County" :required=false />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Postcode" :required=true />
                <x-casemanagement::form-elements.input-text model="postcode" placeholder="Postcode" :required=true />
                <x-casemanagement::form-elements.input-error field="postcode" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Country" :required=false />
                <x-casemanagement::form-elements.input-text model="country" placeholder="Country" :required=false />
            </div>                                                 
            <div>
                <x-casemanagement::form-elements.input-label name="Telephone" :required=false />
                <x-casemanagement::form-elements.input-text model="phone" placeholder="Contact Telephone"
                    :required=false />
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