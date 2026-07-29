<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['totalOpenCases'] }}"
                title="SLA Breached"
                subTitle="SLA Breached" 
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['nearBreach'] }}"
                title="Close To Expiry"
                subTitle="SLA Nearing Limit"
                containerBg="bg-yellow-50"
                containerBorder="border-yellow-100"
                titleText="text-yellow-900"
                subTitleText="text-yellow-600"           
            />            
            <x-casemanagement::page-elements.box-dashboard-element 
                title="Unassigned"
                subTitle="Cases Without Ownership"
                content="{{ $status['unassigned'] }}"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                headerText="text-blue-700"
                titleText="text-blue-900"
                subTitleText="text-blue-600"              
            />                         
        </div>
    
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element
                content="{{ $status['noUpdate'] }}"
                title="No Action"
                subTitle="Cases awaiting update"
                containerBg="bg-gray-50"
                containerBorder="border-gray-100"
                headerText="text-gray-700"
                titleText="text-gray-900"
                subTitleText="text-gray-600"              
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['resolved'] }}"
                title="Resolved"
                subTitle="Cases Resolved"
                containerBg="bg-green-50"
                containerBorder="border-green-100"
                headerText="text-green-700"
                titleText="text-green-900"
                subTitleText="text-green-600"               
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['paused'] }}"
                title="Paused"
                subTitle="SLA Paused"
                containerBg="bg-zinc-50"
                containerBorder="border-zinc-100"
                headerText="text-zinc-700"
                titleText="text-zinc-900"
                subTitleText="text-zinc-600"             
            />                        
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['total'] }}"
                title="Total"
                subTitle="Total All Open Cases"
                containerBg="bg-zinc-50"
                containerBorder="border-zinc-100"
                headerText="text-zinc-700"
                titleText="text-zinc-900"
                subTitleText="text-zinc-600"             
            />              
        </div>

    </div>
</div>
