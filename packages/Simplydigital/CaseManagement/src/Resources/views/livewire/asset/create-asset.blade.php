<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $ui_params['title'] }}" 
        widthsize="{{ $ui_params['widthsize'] }}"
        cancelThisWith="{{ $ui_params['cancelThisWith'] }}" 
        confirmText="{{ $ui_params['confirmText'] }}" 
        confirm="{{ $ui_params['confirm'] }}"
    >
    
        <div class="grid grid-cols-3 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text model="form.short_description" placeholder="Name" :required=true />
                <x-casemanagement::form-elements.input-error field="form.short_description" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Product" :required=true />
                <x-casemanagement::form-elements.select model="form.product_id" name="Product"
                    placeholder="Product" :options=$products />
            </div>            
            <div>
                <x-casemanagement::form-elements.input-label name="Customer" :required=true />
                <x-casemanagement::form-elements.select model="form.customer_id" name="Customer"
                    placeholder="Customer" :options=$customers />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Supplier" :required=true />
                <x-casemanagement::form-elements.select model="form.supplier_id" name="Supplier"
                    placeholder="Supplier" :options=$suppliers />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Buy Price" :required=false />
                <x-casemanagement::form-elements.input-text model="form.buy_price" placeholder="0.00" :required=false />
                <x-casemanagement::form-elements.input-error field="form.buy_price" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Sell Price" :required=false />
                <x-casemanagement::form-elements.input-text model="form.sell_price" placeholder="0.00" :required=false />
                <x-casemanagement::form-elements.input-error field="form.sell_price" />
            </div>
             <div>
                <x-casemanagement::form-elements.input-label name="License Number" :required=false />
                <x-casemanagement::form-elements.input-text model="form.license_number" placeholder="License Number" :required=false />
                <x-casemanagement::form-elements.input-error field="form.license_number" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Location" :required=false />
                <x-casemanagement::form-elements.input-text model="form.location" placeholder="Location" :required=false />
                <x-casemanagement::form-elements.input-error field="form.location" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="IP Address" :required=false />
                <x-casemanagement::form-elements.input-text model="form.ip_address" placeholder="IP Address" :required=false />
                <x-casemanagement::form-elements.input-error field="form.ip_address" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Mac Address" :required=false />
                <x-casemanagement::form-elements.input-text model="form.mac_address" placeholder="Mac Address" :required=false />
                <x-casemanagement::form-elements.input-error field="form.mac_address" />
            </div>                                                                                                                                                                              
            <div>
                <x-casemanagement::form-elements.input-label name="Notes" :required=false />
                <x-casemanagement::form-elements.textarea model="form.notes" placeholder="Notes"
                    :required=false hint="Notes" />
                <x-casemanagement::form-elements.input-error field="form.notes" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Techical Specifications" :required=false />
                <x-casemanagement::form-elements.textarea model="form.technical_specifications" placeholder="Technical Specifications"
                    :required=false hint="Technical Specifications" />
                <x-casemanagement::form-elements.input-error field="form.technical_specifications" />
            </div>
            <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" model="form.active" label="Active" />
            </div>                                                                 
        </div>
    
    </x-casemanagement::page-elements.modal>
</div>