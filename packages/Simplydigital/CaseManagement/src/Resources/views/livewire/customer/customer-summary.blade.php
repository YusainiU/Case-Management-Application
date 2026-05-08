<div>
<div>
    <x-casemanagement::page-elements.modal title="{{ $title }}" widthsize="6xl" cancelThisWith="closeThisModal" :showConfirm="false" cancelText="Close">    
    {{-- <p>{{ dd(get_defined_vars()) }}</p> --}}
        <x-casemanagement::page-elements.description-list-information 
            title="{{ $customer->name }}" 
            description="{{ $customer->short_description }}"
            :items="$customerItems" 
        />
    </x-casemanagement::page-elements.modal>
</div>
</div>