<div>
    @if ($modal)
        <x-casemanagement::page-elements.modal 
            title="{{ $ui_params['title'] }}" 
            widthsize="{{ $ui_params['widthsize'] }}"
            :showConfirm="$displayForm"
            cancelThisWith="{{ $ui_params['cancelThisWith'] }}" 
            confirmText="{{ $ui_params['confirmText'] }}"
            confirm="{{ $ui_params['confirm'] }}"
            
        >
    @else
        <div class="grid grid-cols-1 gap-4 p-4">
    @endif

    @if($updateMode)
        @include('casemanagement::components.form-elements.create-update-visit');
    @else
            @if($displayForm)
                @include('casemanagement::components.form-elements.create-update-visit');
            @else
                <div class="grid grid-rows gap-5">
                    <x-casemanagement::form-elements.input-label  name="Select Case" required="true"/>
                    <x-casemanagement::form-elements.searchable-select
                        name="Case Description"
                        :required="true"
                        :options="$caseOptions"
                        wireModel="caseId"
                    />
                    <x-casemanagement::form-elements.button-primary label="Next" action="selectCase"/>
                    <div>&nbsp;</div>
                    <div>&nbsp;</div>
                    <div>&nbsp;</div>
                </div>                  
            @endif
    @endif


    @if ($modal)
        </x-casemanagement::page-elements.modal>
    @else
        </div>
    @endif

</div>
