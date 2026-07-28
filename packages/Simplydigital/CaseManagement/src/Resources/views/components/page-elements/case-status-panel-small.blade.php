@php
    use Simplydigital\CaseManagement\Services\UI;
    use Carbon\Carbon;
    $important = UI::ALERT_WARNING;
    $information = UI::ALERT_INFORMATION;
    $warning = UI::ALERT_IMPORTANT;
    $paused = UI::PAUSED_STATE_CLASS;
@endphp

@props(['case'])

<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
    <div class="flex flex-wrap items-center gap-3">

        <!-- Expired -->
        @if ($case->slaTasks?->breached)
            <x-casemanagement::page-elements.small-panel-item
                title="{{ $case->slaTasks->sla_flag_text }}"
            />
        @endif

        <!-- Warning -->
        @if ($case->slaTasks?->sla_severity)
            <x-casemanagement::page-elements.small-panel-item
                title="{{ $case->slaTasks->sla_severity }}"
                containerBg="bg-amber-50 border"
                containerBorder="border-amber-100"
                bulletBg="bg-amber-600 animate-pulse"
                paraText="text-gray-500"
            />
        @endif

        <!-- Resolved -->
        @if ($case->is_resolved)
            <x-casemanagement::page-elements.small-panel-item
                title="Resolved"
                containerBg="bg-green-50 border"
                containerBorder="border-green-100"
                bulletBg="bg-green-600"
                paraText="text-green-500"
            />        
        @endif

        <!-- Unassigned -->
        @if ($case->unassigned)
            <x-casemanagement::page-elements.small-panel-item
                title="Unassigned"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                bulletBg="bg-blue-600"
                paraText="text-blue-500"
            />
        @endif

        <!-- No Update -->
        @if (!$case->has_internal_update)
            <x-casemanagement::page-elements.small-panel-item
                title="No Update"
                containerBg="bg-gray-50"
                containerBorder="border-gray-100"
                bulletBg="bg-gray-600"
                paraText="text-gray-500"
            />
        @endif

        <!-- Require Attention -->
        @if ($case->requiresAttention())
            <x-casemanagement::page-elements.small-panel-item
                title="Require Attention"
                containerBg="bg-gray-50"
                containerBorder="border-gray-100"
                bulletBg="{{ $important }}"
                paraText="text-gray-500"
            />
        @endif

        <!-- Paused -->
        @if ($case->slaTasks->activePause)
            <x-casemanagement::page-elements.small-panel-item
                title="SLA Paused"
                containerBg="bg-gray-50"
                containerBorder="border-gray-100"
                bulletBg="{{ $paused }}"
                paraText="text-gray-500"
            />
        @endif

        <!-- Respond By -->
        <x-casemanagement::page-elements.small-panel-item
            title="Respond By: {{ $case->slaTasks->respond_by->format('d-m-Y H:i') }}"
            containerBg="bg-blue-50"
            containerBorder="border-blue-100"
            bulletBg="{{ $information }}"
            paraText="text-blue-500 font-extrabold"
        />

        @if ($case->slaTasks->responded_at)
            <x-casemanagement::page-elements.small-panel-item
                title="Responded At: {{ $case->slaTasks->responded_at->format('d-m-Y H:i') }}"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                bulletBg="{{ $information }}"
                paraText="text-blue-500 font-extrabold"
            />
        @endif


        @if ($case->outstandingVisit()->exists())
            <!-- Visit Scheduled -->
            <x-casemanagement::page-elements.small-panel-item
                title="Visit Scheduled"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                bulletBg="{{ $information }}"
                paraText="text-blue-500 font-extrabold"
            />
        @endif

        @if ($case->isResolved)
            <!-- Resolved -->
            <x-casemanagement::page-elements.small-panel-item
                title="Resolved At {{ Carbon::createFromTimestamp($case->resolved_datetime)->format('d-m-Y H:i') }}"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                bulletBg="{{ $information }}"
                paraText="text-blue-500 font-extrabold"
            />
        @endif
        
        @if ($case->isClosed)
            <!-- Closed -->
            <x-casemanagement::page-elements.small-panel-item
                title="CLOSED"
                containerBg="bg-red-50"
                containerBorder="border-red-100"
                bulletBg="{{ $warning }}"
                paraText="text-red-500 font-extrabold"
            />            
        @endif
    
        @if($case->closedCase)
            <!-- Open from closed ticket -->
            <x-casemanagement::page-elements.small-panel-item
                title="REOPENED: {{ $case->closedCase->number }}"
                containerBg="bg-blue-50"
                containerBorder="border-blue-100"
                bulletBg="{{ $information }}"
                paraText="text-blue-500 font-extrabold"
            />
        @endif

    </div>
</div>
