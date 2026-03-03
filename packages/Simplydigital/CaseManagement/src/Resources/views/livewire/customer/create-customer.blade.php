<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $title }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal" 
        confirmText="Create"
        confirm="save"
    >

    <div class="flex flex-row gap-2 w-full">
        <div class="flex flex-row gap-8">
            <div class="flex flex-col gap-2">
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text modal="name" placeholder="Customer Name" :required=true />
            </div>
            <div class="flex flex-col gap-2">
                <x-casemanagement::form-elements.input-label name="Address 1" :required=true />
                <x-casemanagement::form-elements.input-text modal="address_1" placeholder="Address 1" :required=true />
            </div>            
        </div>
    </div>

    </x-casemanagement::page-elements.modal>
</div>