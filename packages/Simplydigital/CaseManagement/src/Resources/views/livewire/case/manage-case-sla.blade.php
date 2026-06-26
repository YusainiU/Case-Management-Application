<div>
    <div>
        @if($this->message)
            <x-casemanagement::page-elements.toast-alert-simple 
                header="Changes Made"
                mode="information"
                message="{{ $message }}"
            />
        @endif
        @if($this->noSlaTask)
            <x-casemanagement::page-elements.toast-alert-simple 
                header="WARNING"
                mode="warning"
                message="SLA Task is missing"
            />
        @endif
    </div>
    <div class="grid grid-cols-3 gap-4 mb-4">     
        <div>
            <x-casemanagement::form-elements.input-label name="Assign To" :required=false />
            @if($forms['assigned_to'])
                <x-casemanagement::form-elements.input-text value="{{ $case->assignedToUser?->name }}" :disabled=true />
                <x-casemanagement::form-elements.input-text model="forms.assigned_to" type="hidden"/>
            @else            
                <x-casemanagement::form-elements.select 
                model="forms.assigned_to"
                :notlive=true
                :options=$internals 
                name="Assigned To" 
                placeholder="Assigned To"
                hint="Case Assigned To ... "
                />
            @endif
            <x-casemanagement::form-elements.input-error field="forms.assigned_to" />
        </div>           
        <div>
            <x-casemanagement::form-elements.input-label name="Responded At" :required=false />
            <x-casemanagement::form-elements.input-text 
                type="datetime-local"
                model="forms.responded_at"
                name="Responded At"
                webkit="
                [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                [&::-webkit-calendar-picker-indicator]:p-1 
                [&::-webkit-calendar-picker-indicator]:rounded"
                @if($case->slaTasks->activePause())
                    :disabled=true
                    hint="The SLA is Paused. To change unpause first"
                @else
                    hint="The date and time when the case was fixed."
                @endif
            />
             <x-casemanagement::form-elements.input-error field="forms.responded_at" />
        </div>
        <div>
            <x-casemanagement::form-elements.input-label name="Fixed At" :required=false />
            <x-casemanagement::form-elements.input-text 
                type="datetime-local"
                model="forms.fixed_at"
                name="Fixed At"
                webkit="
                [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                [&::-webkit-calendar-picker-indicator]:p-1 
                [&::-webkit-calendar-picker-indicator]:rounded"
                @if($case->slaTasks->activePause())
                    :disabled=true
                    hint="The SLA is Paused. To change unpause first"
                @else
                    hint="The date and time when the case was fixed."
                @endif
            />
            <x-casemanagement::form-elements.input-error field="forms.fixed_at" />
        </div>
        <div>
            <x-casemanagement::form-elements.input-label name="Progress Note" />
            <x-casemanagement::form-elements.textarea model="forms.log" hint="Progress Note"/>
            <x-casemanagement::form-elements.input-error field="forms.log" />
        </div>
    </div>
    <div class="flex flex-row gap-4">
        @if($this->noSlaTask)
            <x-casemanagement::form-elements.button-danger action="createSlaTask" label="Create SLA Task" />
        @else
            <x-casemanagement::form-elements.button-primary action="updateSla" label="Update" />
            @if($forms['assigned_to'])
                <x-casemanagement::form-elements.button-danger action="unassign" label="Unassigned Case" />
            @endif
        @endif
    </div>      
</div>