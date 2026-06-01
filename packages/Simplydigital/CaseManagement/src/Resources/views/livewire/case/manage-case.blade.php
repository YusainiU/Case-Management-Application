<div>
    <x-casemanagement::page-elements.modal 
        title="{{ $ui['title'] }}"
        description="{{ $ui['description'] }}" 
        widthsize="6xl" 
        cancelThisWith="closeThisModal" 
        :showConfirm="false" 
        cancelText="Close"
    >
        <x-casemanagement::page-elements.paragraph>
            <span class="font-bold block mb-4">Case Description:</span>
            {{ $case->description }}
        </x-casemanagement::page-elements.paragraph>
        <x-casemanagement::page-elements.seperator margin="0" />
        <x-casemanagement::page-elements.tab 
            :tabHeaders=$tabHeaders
            selectedTab="{{ $selectedTab }}" 
            tabBgColorActive="bg-200" 
            tabBgColorInactive="bg-200"
            tabBgColorInactiveHover="hover:bg-300"
            tabBgHover="hover:bg-300 hover:text-gray-900"
        >
            <x-slot name="tabSelectedComponent">
                Selected Tab
            </x-slot>
        </x-casemanagement::page-elements.tab>

    </x-casemanagement::page-elements.modal>    
</div>