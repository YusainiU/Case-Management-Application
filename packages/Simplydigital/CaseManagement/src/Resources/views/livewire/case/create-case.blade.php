<div>

        @if($displayForm)
            <x-casemanagement::page-elements.modal title="{{ $ui_params['title'] }}" widthsize="{{ $ui_params['widthsize'] }}"
                cancelThisWith="{{ $ui_params['cancelThisWith'] }}" confirmText="{{ $ui_params['confirmText'] }}"
                confirm="{{ $ui_params['confirm'] }}"
            >
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-casemanagement::form-elements.input-label name="Customer Selected" :required=false />
                        <x-casemanagement::form-elements.input-text value="{{ $customerSelected->name }}" disabled :required=false />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Select SLA" :required=true />
                        <x-casemanagement::form-elements.select model="form.sla_applications_id"
                            name="Service Level Agreement" placeholder="Service Level Agreement" :options=$slapOptions 
                        />
                        <x-casemanagement::form-elements.input-error field="form.sla_applications_id" />
                    </div>               
                    <div>
                        <x-casemanagement::form-elements.input-label name="Category" :required=true />
                        <x-casemanagement::form-elements.select model="form.category"
                            name="Category" placeholder="Category" :options=$categoryOptions 
                        />
                        <x-casemanagement::form-elements.input-error field="form.category" />
                    </div>                    
                    <div>
                        <x-casemanagement::form-elements.input-label name="Source" :required=true />
                        <x-casemanagement::form-elements.select model="form.source"
                            name="Source" placeholder="Source" :options=$sourceOptions 
                        />
                        <x-casemanagement::form-elements.input-error field="form.category" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Select Assets" :required=false />
                        <x-casemanagement::form-elements.multiselect :options=$assetOptions placeholder="Select Assets"  wire:model="form.list_of_assets" />                      
                    </div>  
                    <div>
                        <x-casemanagement::form-elements.input-label name="Customer Contact (User)" :required=false />
                        <x-casemanagement::form-elements.select model="form.customer_contact_user"
                            name="Customer Contact User" placeholder="Customer Contact (User)" :options=$contactOptions 
                        />
                        <x-casemanagement::form-elements.input-error field="form.customer_contact_user" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Customer Contact (Non-User)" :required=false />
                        <x-casemanagement::form-elements.input-text model="form.customer_contact_nonuser" placeholder="Customer Contact (Non-User)" :required=false />
                        <x-casemanagement::form-elements.input-error field="form.customer_contact_nonuser" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Telephone" :required=true />
                        <x-casemanagement::form-elements.input-text model="form.contact_telephone" placeholder="Contact Telephone" :required=false />
                        <x-casemanagement::form-elements.input-error field="form.contact_telephone" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Email" :required=true />
                        <x-casemanagement::form-elements.input-text model="form.contact_email" placeholder="Contact Email" :required=true />
                        <x-casemanagement::form-elements.input-error field="form.contact_email" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Customer Reference" :required=false />
                        <x-casemanagement::form-elements.input-text model="form.customer_reference" placeholder="Customer Reference" :required=false />
                        <x-casemanagement::form-elements.input-error field="form.customer_reference" />
                    </div>
                    <div>
                        <x-casemanagement::form-elements.input-label name="Case Description" :required=true />
                        <x-casemanagement::form-elements.textarea model="form.description" placeholder="Case Descriptin"
                            :required=true hint="Case Description" />
                        <x-casemanagement::form-elements.input-error field="form.description" />
                    </div>                                                                                                    
                </div>                    
                

            </x-casemanagement::page-elements.modal>
        @else
            <x-casemanagement::page-elements.modal title="{{ $ui_params['title'] }}" widthsize="{{ $ui_params['widthsize'] }}"
                cancelThisWith="{{ $ui_params['cancelThisWith'] }}" :showFooter=false
            >
                <div class="grid grid-rows gap-5">
                    <x-casemanagement::form-elements.searchable-select
                        name="Customer"
                        :required="true"
                        :options="$customerOptions"
                        wireModel="customerId"
                    />
                    <x-casemanagement::form-elements.button-primary label="Next" action="selectCustomer"/>
                    <div>&nbsp;</div>
                    <div>&nbsp;</div>
                    <div>&nbsp;</div>
                </div>                
            </x-casemanagement::page-elements.modal>            
            
        @endif
    
    

</div>