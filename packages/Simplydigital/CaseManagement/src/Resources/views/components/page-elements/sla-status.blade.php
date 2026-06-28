<div class="grid grid-cols-3 gap-4">
    <div>
        <x-casemanagement::form-elements.input-label name="Respond By" />
        <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->respond_by->format('d-m-Y H:i') }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="SLA Status" />
        <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->sla_flag_text }}"
            textCss="{{ $case->slaTasks->sla_flag }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Duration" />
        <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->sla_status_info }}"
            textCss="{{ $case->slaTasks->sla_flag }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="SLA Applied" />
        <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->slaApplication?->name }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Start Day" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->start_day->label() }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="End Day" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->end_day->label() }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Start of Service" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->getServiceStartTime() }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="End of Service" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->getServiceEndTime() }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Include Public Holidays?" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->include_public_holidays ? 'Yes' : 'No' }}" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Response Time" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->getResponseTime() }} hours" />
    </div>
    <div>
        <x-casemanagement::form-elements.input-label name="Fixed Time" />
        <x-casemanagement::page-elements.text-box
            text="{{ $case->slaTasks->slaApplication->serviceLevelAgreement->getFixedTime() }} hours" />
    </div>
</div>
