<div>

    <div class="grid grid-cols-1 gap-4">
        <div class="flex flex-col gap-2 col-span-3">
            <x-casemanagement::form-elements.input-label name="Short Description" :required=true />
            <x-casemanagement::form-elements.input-text
                model="form.short_description"
                name="Short Description"
                placeholder="Short Description"
                hint="Title/Short Description ... "
            />
        </div>        
    </div>    
    <div class="grid grid-cols-3 gap-4">
        <div class="flex flex-col gap-2">
            <x-casemanagement::form-elements.input-label name="Case Selected for {{ $case->customer->name }}" :required=false />
            <x-casemanagement::form-elements.input-text value="{{  Str::limit($case->short_description, 30) }}" disabled :required=false />
        </div>
        <div class="flex flex-col gap-2">
            <x-casemanagement::form-elements.input-label name="Visit Scheduled At" :required=true />
            <x-casemanagement::form-elements.input-text 
                type="datetime-local"
                model="form.visit_scheduled_at"
                name="Visit Scheduled At"
                webkit="
                [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                [&::-webkit-calendar-picker-indicator]:p-1 
                [&::-webkit-calendar-picker-indicator]:rounded"
            />                        
        </div>
        <div class="flex flex-col gap-2">
            <x-casemanagement::form-elements.input-label name="Visit Assigned To" :required=false />
            <x-casemanagement::form-elements.select 
                model="form.assigned_to"
                :notlive=true
                :options=$userOptions 
                name="Assigned To" 
                placeholder="Assigned To"
                hint="Visit Assigned To ... "
            />
        </div>
        @if($updateMode)
            @if($taskSiteVisit?->visitScheduled())
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Engineer Enroute At" :required=false />
                    <x-casemanagement::form-elements.input-text 
                        type="datetime-local"
                        model="form.enroute_at"
                        name="Engineer Enroute At"
                        webkit="
                        [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                        [&::-webkit-calendar-picker-indicator]:p-1 
                        [&::-webkit-calendar-picker-indicator]:rounded"
                    />                        
                </div>
            @endif
            @if($taskSiteVisit?->visitEnroute())
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Engineer Onsite At" :required=false />
                    <x-casemanagement::form-elements.input-text 
                        type="datetime-local"
                        model="form.onsite_at"
                        name="Engineer Onsite At"
                        webkit="
                        [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                        [&::-webkit-calendar-picker-indicator]:p-1  
                        [&::-webkit-calendar-picker-indicator]:rounded"
                    />
                </div>
            @endif
            @if($taskSiteVisit?->visitOnsite())
                <div class="flex flex-col gap-2">
                    <x-casemanagement::form-elements.input-label name="Engineer Offsite At" :required=false />
                    <x-casemanagement::form-elements.input-text 
                        type="datetime-local"
                        model="form.offsite_at"
                        name="Engineer Offsite At"
                        webkit="
                        [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                        [&::-webkit-calendar-picker-indicator]:p-1  
                        [&::-webkit-calendar-picker-indicator]:rounded"
                    />
                </div>
            @endif
        @endif
    </div>
    <div class="grid grid-cols-1 gap-4">
        <div class="flex flex-col gap-2 col-span-3">
            <x-casemanagement::form-elements.input-label name="Visit Notes" :required=false />
            <x-casemanagement::form-elements.textarea 
                model="form.description"
                name="Visit Notes"
                placeholder="Visit Notes"
                hint="Visit Notes ... "
            />
        </div>        
    </div>
    @if($updateMode)
        <div class="grid grid-cols-3 gap-4 mt-4">
            @if($completed)                
                <x-casemanagement::form-elements.button-danger label="Cancel/Remove Visit" action="removeVisit" />
            @else
                <x-casemanagement::form-elements.button-primary label="Complete Visit" action="completeVisit" />
                <x-casemanagement::form-elements.button-primary label="Update" action="save" />
            @endif
        </div>
    @endif
</div>