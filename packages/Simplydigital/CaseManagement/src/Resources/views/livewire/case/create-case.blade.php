<div>

        @if($displayForm)
            @if($modeUpdate)
                @include('casemanagement::components.form-elements.create-update-case');
            @else
                <x-casemanagement::page-elements.modal title="{{ $ui_params['title'] }}" widthsize="{{ $ui_params['widthsize'] }}"
                    cancelThisWith="{{ $ui_params['cancelThisWith'] }}" confirmText="{{ $ui_params['confirmText'] }}"
                    confirm="{{ $ui_params['confirm'] }}"
                >
                    @include('casemanagement::components.form-elements.create-update-case');                
                </x-casemanagement::page-elements.modal>
            @endif
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