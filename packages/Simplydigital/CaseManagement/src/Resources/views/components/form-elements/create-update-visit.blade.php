    <div class="grid grid-cols-3 gap-4">
        <div>
            <x-casemanagement::form-elements.input-label name="Case Selected" :required=false />
            <x-casemanagement::form-elements.input-text value="{{ $case->short_description }}" disabled :required=false />
        </div>
    </div>
