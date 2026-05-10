<div>

    <x-casemanagement::page-elements.modal 
        title="{{ $ui_params['title'] }}" 
        widthsize="{{ $ui_params['widthsize'] }}"
        cancelThisWith="{{ $ui_params['cancelThisWith'] }}" 
        confirmText="{{ $ui_params['confirmText'] }}" 
        confirm="{{ $ui_params['confirm'] }}"
    >

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Customer" :required=true />
                <x-casemanagement::form-elements.select model="form.customer_id" name="Customer"
                    placeholder="Customer" :options=$customers />
                <x-casemanagement::form-elements.input-error field="form.customer_id" />
            </div>                 
            <div>
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text model="form.name" placeholder="Name" :required=true />
                <x-casemanagement::form-elements.input-error field="form.name" />

            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Service Level Agreement" :required=true />
                <x-casemanagement::form-elements.select model="form.service_level_agreement_id" name="Service Level Agreement"
                    placeholder="Service Level Agreement" :options=$slas />
                <x-casemanagement::form-elements.input-error field="form.service_level_agreement_id" />
            </div>            
            <div>
                <x-casemanagement::form-elements.input-label name="Priority Agreement" :required=true />
                <x-casemanagement::form-elements.select model="form.priority" name="Priority"
                    placeholder="Priority" :options=$priorities />
                <x-casemanagement::form-elements.input-error field="form.priority" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Description" :required=false />
                <x-casemanagement::form-elements.textarea model="form.short_description" placeholder="Short Description"
                    :required=false hint="Technical Specifications" />
                <x-casemanagement::form-elements.input-error field="form.short_description" />
            </div>
            <div></div>            
           <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" model="form.active" label="Active" />
            </div>                          
        </div>
    </x-casemanagement::page-elements.modal>
</div>