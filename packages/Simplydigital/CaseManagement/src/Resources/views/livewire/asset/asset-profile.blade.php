<div>
    <x-casemanagement::page-elements.modal title="{{ $title }}" widthsize="6xl" cancelThisWith="closeThisModal" :showConfirm="false" cancelText="Close">    
    {{-- <p>{{ dd(get_defined_vars()) }}</p> --}}
        <x-casemanagement::page-elements.description-list-information 
            title="{{ $asset->short_description }}" 
            description="{{ $asset->number }}"
            :items="$assetItems" 
        />
    </x-casemanagement::page-elements.modal>
</div>