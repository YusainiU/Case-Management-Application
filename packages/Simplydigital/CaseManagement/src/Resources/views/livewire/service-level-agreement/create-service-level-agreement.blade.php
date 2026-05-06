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
                <x-casemanagement::form-elements.input-text model="form.name" placeholder="Name" :required=true />
                <x-casemanagement::form-elements.input-error field="form.name" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Type" :required=true />
                <x-casemanagement::form-elements.select model="form.type" name="type"
                    placeholder="Type" :options=$types />
            </div>            
            <div>
                <x-casemanagement::form-elements.input-label name="Start Day" :required=true />
                <x-casemanagement::form-elements.select model="form.start_day" name="start_day"
                    placeholder="Start Day" :options=$daysOfWeek />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="End Day" :required=true />
                <x-casemanagement::form-elements.select model="form.end_day" name="end_day"
                    placeholder="End Day" :options=$daysOfWeek />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Service Start At" :required=true />
                <x-casemanagement::form-elements.input-text model="form.service_start_time" type="time" value="00:00" :required=true />
                <x-casemanagement::form-elements.input-error field="form.service_start_at" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Service End At" :required=true />
                <x-casemanagement::form-elements.input-text model="form.service_end_time" type="time" value="00:00" :required=true />
                <x-casemanagement::form-elements.input-error field="form.service_end_at" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Response Time (Hours)" :required=true />
                <x-casemanagement::form-elements.input-text 
                    model="form.response_time" 
                    type="text"
                    width="w-16"
                    maxlength="2"
                    inputmode="numeric"
                    :required=true />
                <x-casemanagement::form-elements.input-error field="form.response_time" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Fixed Time (Hours)" :required=true />
                <x-casemanagement::form-elements.input-text 
                    model="form.fixed_time" 
                    type="text"
                    width="w-16"
                    maxlength="2"
                    inputmode="numeric"
                    :required=true />
                <x-casemanagement::form-elements.input-error field="form.fixed_time" />
            </div>
            <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" model="form.active" label="Active" />
                <x-casemanagement::form-elements.checkbox name="Include Public Holiday" model="form.include_public_holiday" label="Include Public Holiday?" />
            </div>                
            <div>
                <x-casemanagement::form-elements.input-label name="Description" :required=false />
                <x-casemanagement::form-elements.textarea model="form.short_description" placeholder="Short Description"
                    :required=false hint="Short Description" />
                <x-casemanagement::form-elements.input-error field="form.short_description" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Cover Details" :required=false />
                <x-casemanagement::form-elements.textarea model="form.cover_details" placeholder="Cover Details"
                    :required=false hint="Cover Details" />
                <x-casemanagement::form-elements.input-error field="cover_details" />
            </div>
                                 
            
        </div>

    </x-casemanagement::page-elements.modal>    
</div>