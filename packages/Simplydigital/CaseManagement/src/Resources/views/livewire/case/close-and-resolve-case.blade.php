<div>
    @if ($this->message)
        <x-casemanagement::page-elements.toast-alert-simple header="Changes Made" mode="information"
            message="{{ $message }}" />
    @endif
    <div class="grid grid-cols-3 gap-4">
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.input-label name="Resolution" :required=true />
            <x-casemanagement::form-elements.select model="form_resolve.resolution" name="resolution"
                placeholder="Resolution" :options=$resolutions :disabled="$isResolved" />
        </div>
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.input-label name="Resolved At" />
            <x-casemanagement::form-elements.input-text type="datetime-local" model="form_resolve.resolved_datetime"
                name="Resolved At"
                webkit="
                [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                [&::-webkit-calendar-picker-indicator]:p-1 
                [&::-webkit-calendar-picker-indicator]:rounded"
                :disabled="$isResolved" hint="Date and time when the case is resolved." />
        </div>
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.input-label name="Resolved By" :required=true />
            <x-casemanagement::form-elements.select model="form_resolve.resolved_by" name="Resolved By"
                placeholder="Resolved By" :options=$resolvers :disabled="$isResolved" />
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.input-label name="Resolution Notes" />
            <x-casemanagement::form-elements.textarea model="form_resolve.resolution_details" hint="Resolution Notes"
                :disabled="$isResolved" />
        </div>
    </div>
    <div class="grid grid-cols-3 gap-4 mt-4">
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.input-label name="Case Closed At" />
            <x-casemanagement::form-elements.input-text type="datetime-local" model="form_close.closed_datetime"
                name="closed_at"
                webkit="
                [&::-webkit-calendar-picker-indicator]:bg-gray-400 
                [&::-webkit-calendar-picker-indicator]:p-1 
                [&::-webkit-calendar-picker-indicator]:rounded"
                :disabled="$isClosed || (!$isResolved && !$isClosed)" hint="This will be available once the case is resolved"
                hint_important="font-extrabold" />
        </div>
    </div>
    @can('update', $case)
        @if (!$isClosed)
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="flex flex-col gap-3">
                    <x-casemanagement::form-elements.button-primary label="Save" action="save" />
                </div>
            </div>
        @else
            @if (!$case->getOpenCase())
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <x-casemanagement::form-elements.button-primary label="Reopen Case" action="reopen" />
                </div>
            @endif
        @endif
    @endcan
</div>
