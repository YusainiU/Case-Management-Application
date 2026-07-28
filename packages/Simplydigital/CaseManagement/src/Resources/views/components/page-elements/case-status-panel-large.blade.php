@props([
    'status' => [],
])


<!-- Status Summary Panel -->
<div class="w-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

    <!-- Header -->
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Case Status Overview</h2>
            <p class="text-sm text-gray-500">
                Monitor response SLA and case handling status
            </p>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <x-casemanagement::form-elements.button-generic 
                label="All Cases"
                action="allOpenCases"
            />
            <x-casemanagement::form-elements.button-generic
                label="Expired"
                bg="bg-red-50"
                text="text-red-700"
                hover="bg-red-100"
                action="allBreachedCases"
            />
            <x-casemanagement::form-elements.button-generic
                label="Warning"
                bg="bg-yellow-50"
                text="text-yellow-700"
                hover="bg-yellow-100"
                action="allNearBreachCases"
            />
            <x-casemanagement::form-elements.button-generic
                label="Resolved"
                bg="bg-green-50"
                text="text-green-700"
                hover="bg-green-100"
                action="allResolvedCases"
            />
            <x-casemanagement::form-elements.button-generic
                label="Paused"
                bg="bg-olive-50"
                text="text-olive-700"
                hover="bg-olive-100"
                action="allPausedCases"
            />
            <x-casemanagement::form-elements.button-generic
                label="Closed"
                bg="bg-red-800"
                text="text-gray-100"
                hover="bg-red-600"
                action="allClosedCases"
            />              
            <x-casemanagement::form-elements.button-generic
                label="Reset"
                bg="bg-gray-800"
                text="text-gray-100"
                hover="bg-gray-600"
                action="resetFilterSelection"
            />                        
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        <!-- Expired -->
        <x-casemanagement::page-elements.box-element 
            title="Expired"
            status="{{ $status['totalBreached'] }}"
            label="SLA Breached" 
        /> 

        <!-- Warning -->
        <x-casemanagement::page-elements.box-element
            title="Close To Expiry"
            status="{{ $status['nearBreach'] }}"
            label="SLA Nearing Limit"
            containerBg="bg-yellow-50"
            containerBorder="border-yellow-100"
            titleText="text-yellow-900"
            subTitleText="text-yellow-600"
        />

        <!-- Unassigned -->
        <x-casemanagement::page-elements.box-element
            title="Unassigned"
            status="{{ $status['unassigned'] }}"
            label="Awaiting Owner"
            containerBg="bg-blue-50"
            containerBorder="border-blue-100"
            headerText="text-blue-700"
            titleText="text-blue-900"
            subTitleText="text-blue-600"
        />

        <!-- No Action -->
        <x-casemanagement::page-elements.box-element
            title="No Action"
            status="{{ $status['noUpdate'] }}"
            label="No Update"
            containerBg="bg-gray-50"
            containerBorder="border-gray-100"
            headerText="text-gray-700"
            titleText="text-gray-900"
            subTitleText="text-gray-600"
        />        

        <!-- Resolved -->
        <x-casemanagement::page-elements.box-element
            title=" Resolved"
            status="{{ $status['resolved'] }}"
            label="Case Resolved"
            containerBg="bg-green-50"
            containerBorder="border-green-100"
            headerText="text-green-700"
            titleText="text-green-900"
            subTitleText="text-green-600"
        />

        <!-- Paused -->
        <x-casemanagement::page-elements.box-element
            title=" Paused"
            status="{{ $status['paused'] }}"
            label="SLA Paused"
            containerBg="bg-zinc-50"
            containerBorder="border-zinc-100"
            headerText="text-zinc-700"
            titleText="text-zinc-900"
            subTitleText="text-zinc-600"
        />
        
        <!-- Visit Schduled -->
        <x-casemanagement::page-elements.box-element
            title="Site Visits"
            status="{{ $status['totalVisits'] }}"
            label="Site Visits"
            containerBg="bg-rose-50"
            containerBorder="border-rose-100"
            headerText="text-rose-700"
            titleText="text-rose-900"
            subTitleText="text-rose-600"
        />
                
    </div>
</div>
