<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $ui['title'] }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal" 
        :showConfirm="false" 
        cancelText="Close"
    >
        <div class="grid grid-cols-3 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Respond By"/>
                <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->respond_by->format('d-m-Y H:i') }}" />
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="SLA Status"/>
                <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->sla_flag_text }}" textCss="{{ $case->slaTasks->sla_flag }}"/>
            </div>
            <div>
                <x-casemanagement::form-elements.input-label name="Duration"/>
                <x-casemanagement::page-elements.text-box text="{{ $case->slaTasks->sla_status_info }}" textCss="{{ $case->slaTasks->sla_flag }}" />
            </div>
        </div>
    </x-casemanagement::page-elements.modal>
</div>