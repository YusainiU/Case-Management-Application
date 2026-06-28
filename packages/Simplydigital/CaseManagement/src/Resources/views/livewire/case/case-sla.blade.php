<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $ui['title'] }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal" 
        :showConfirm="false" 
        cancelText="Close"
    >
         <x-casemanagement::page-elements.sla-status :case="$case" />
    </x-casemanagement::page-elements.modal>
</div>