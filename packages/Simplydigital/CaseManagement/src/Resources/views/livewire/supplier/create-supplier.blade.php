<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $ui_params['title'] }}" 
        widthsize="6xl"
        cancelThisWith="closeThisModal" 
        confirmText="{{ $ui_params['confirmText'] }}" 
        confirm="save"
    >
    
    <div class="grid grid-cols-2 gap-4">

        <div>
            <x-casemanagement::form-elements.input-label name="Name" :required=true />
            <x-casemanagement::form-elements.input-text model="name" placeholder="Supplier Name" :required=true />
            <x-casemanagement::form-elements.input-error field="name" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="Email" :required=false />
            <x-casemanagement::form-elements.input-text model="email" placeholder="Email Address" :required=false />
            <x-casemanagement::form-elements.input-error field="email" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="Telephone" :required=false />
            <x-casemanagement::form-elements.input-text model="telephone" placeholder="Telephone Number" :required=false />
            <x-casemanagement::form-elements.input-error field="telephone" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="Address 1" :required=false />
            <x-casemanagement::form-elements.input-text model="address_1" placeholder="Supplier Address 1" :required=false hint="Supplier Address 1" />
            <x-casemanagement::form-elements.input-error field="address_1" />
        </div>
        
        <div>
            <x-casemanagement::form-elements.input-label name="Address 2" :required=false />
            <x-casemanagement::form-elements.input-text model="address_2" placeholder="Supplier Address 2" :required=false hint="Supplier Address 2" />
            <x-casemanagement::form-elements.input-error field="address_2" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="Address 3" :required=false />
            <x-casemanagement::form-elements.input-text model="address_3" placeholder="Supplier Address 3" :required=false hint="Supplier Address 3" />
            <x-casemanagement::form-elements.input-error field="address_3" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="Address 4" :required=false />
            <x-casemanagement::form-elements.input-text  model="address_4" placeholder="Supplier Address 4" :required=false hint="Supplier Address 4" />
            <x-casemanagement::form-elements.input-error field="address_4" />
        </div>

        <div>
            <x-casemanagement::form-elements.input-label name="URL" :required=false />
            <x-casemanagement::form-elements.input-text model="url" placeholder="Supplier URL" :required=false />
            <x-casemanagement::form-elements.input-error field="url" />
        </div>

        <div class="flex flex-col gap-4 justify-items-start">
            <x-casemanagement::form-elements.checkbox name="Active" :value=$active model="active" label="Active" />
        </div>

    </div>    
    
    </x-casemanagement::page-elements.modal>
</div>