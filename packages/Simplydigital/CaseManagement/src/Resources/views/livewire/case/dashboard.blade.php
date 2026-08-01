@php
    use Simplydigital\CaseManagement\Services\UI;
    $warning = UI::ALERT_WARNING;
    $danger = UI::ALERT_DANGER;
    $information = UI::ALERT_INFORMATION;
    $important = UI::ALERT_IMPORTANT;
    $success = UI::ALERT_SUCCESS;
    $default = UI::ALERT_DEFAULT;
@endphp

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['totalOpenCases'] }}"
                title="SLA Breached"
                subTitle="SLA Breached"
                action="openBreached" 
                containerBg="{{ $danger }}"
                containerBorder=" "
                titleText=" "
                subTitleText=" "                
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['nearBreach'] }}"
                title="Close To Expiry"                
                subTitle="SLA Nearing Limit"
                action="openNearBreach"
                containerBg="{{ $warning }}"
                containerBorder=" "
                titleText=" "
                subTitleText=" "           
            />            
            <x-casemanagement::page-elements.box-dashboard-element 
                title="Unassigned"
                subTitle="Cases Without Ownership"
                content="{{ $status['unassigned'] }}"
                action="openUnassigned"
                containerBg="{{ $information }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=" "              
            />                         
        </div>
    
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element
                content="{{ $status['noUpdate'] }}"
                title="No Action"
                subTitle="Cases awaiting update"
                action="openNoUpdate"
                containerBg="{{ $important }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=""              
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['resolved'] }}"
                title="Resolved"
                subTitle="Cases Resolved"
                action="openResolvedCases"
                containerBg="{{ $success }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=" "               
            />
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['paused'] }}"
                title="Paused"
                subTitle="SLA Paused"
                action="openPaused"
                containerBg="{{ $warning }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=" "             
            />                        
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element 
                content="{{ $status['total'] }}"
                title="Total"
                subTitle="Total All Open Cases"
                action="openAllOutstanding"
                containerBg="{{ $default }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=" "             
            />
            <x-casemanagement::page-elements.box-dashboard-element
                title="Site Visits"
                content="{{ $status['totalVisits'] }}"
                subTitle="Site Visits"
                action="openVisit"
                containerBg="{{ $information }}"
                containerBorder=" "
                headerText=" "
                titleText=" "
                subTitleText=" "
            />                   
        </div>

    </div>
</div>
