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
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 border border-red-100">
                <span class="w-3 h-3 rounded-full bg-red-600"></span>
                <div>
                    <p class="text-xs text-gray-500">{{ $case->slaTasks->sla_flag_text }}</p>
                    {{-- <p class="text-sm font-semibold text-red-700">12 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Warning -->
        @if ($case->slaTasks?->sla_severity)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-100">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                <div>
                    <p class="text-xs text-gray-500">{{ $case->slaTasks->sla_severity }}</p>
                    {{-- <p class="text-sm font-semibold text-amber-700">7 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Resolved -->
        @if ($case->is_resolved)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-green-50 border border-green-100">
                <span class="w-3 h-3 rounded-full bg-green-600"></span>
                <div>
                    <p class="text-xs text-gray-500">Resolved</p>
                    {{-- <p class="text-sm font-semibold text-green-700">48 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Unassigned -->
        @if ($case->unassigned)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                <div>
                    <p class="text-xs text-gray-500">Unassigned</p>
                    {{-- <p class="text-sm font-semibold text-blue-700">5 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- No Update -->
        @if (!$case->has_internal_update)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                <span class="w-3 h-3 rounded-full bg-gray-500"></span>
                <div>
                    <p class="text-xs text-gray-500">No Update</p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Require Attention -->
        @if ($case->requiresAttention())
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                <span class="w-3 h-3 rounded-full {{ $important }}"></span>
                <div>
                    <p class="text-xs text-gray-500">Require Attention</p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Paused -->
        @if ($case->slaTasks->activePause)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                <span class="w-3 h-3 rounded-full {{ $information }}"></span>
                <div>
                    <p class="text-xs text-gray-500">SLA PAUSED</p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif

        <!-- Respond By -->
        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
            <span class="w-3 h-3 rounded-full {{ $information }}"></span>
            <div>
                <p class="text-xs text-gray-500 font-extrabold">Respond By:
                    {{ $case->slaTasks->respond_by->format('d-m-Y H:i') }}</p>
                {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
            </div>
        </div>

        @if ($case->slaTasks->responded_at)
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                <span class="w-3 h-3 rounded-full {{ $information }}"></span>
                <div>
                    <p class="text-xs text-gray-500 font-extrabold">Responded At:
                        {{ $case->slaTasks->responded_at->format('d-m-Y H:i') }}</p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif


        @if ($case->outstandingVisit()->exists())
            <!-- Visit Scheduled -->
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                <span class="w-3 h-3 rounded-full {{ $information }}"></span>
                <div>
                    <p class="text-xs text-gray-500 font-extrabold">Visit Scheduled</p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif

        @if ($case->isResolved)
            <!-- Resolved -->
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                <span class="w-3 h-3 rounded-full {{ $information }}"></span>
                <div>
                    <p class="text-xs text-gray-500 font-extrabold">
                        Resolved at {{ Carbon::createFromTimestamp($case->resolved_datetime)->format('d-m-Y H:i') }}
                    </p>
                    {{-- <p class="text-sm font-semibold text-gray-700">14 Cases</p> --}}
                </div>
            </div>
        @endif
        
        @if ($case->isClosed)
            <!-- Closed -->
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 border border-red-100">
                <span class="w-3 h-3 rounded-full {{ $warning }}"></span>
                <div>
                    <p class="text-xs text-gray-500 font-extrabold">
                        ClOSED
                    </p>
                </div>
            </div>
        @endif
    
        @if($case->closedCase)
            <!-- Open from closed ticket -->
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                <span class="w-3 h-3 rounded-full {{ $information }}"></span>
                <div>
                    <p class="text-xs text-gray-500 font-extrabold">
                        REOPENED: {{ $case->closedCase->number }}
                    </p>
                </div>
            </div>
        @endif

    </div>
</div>
