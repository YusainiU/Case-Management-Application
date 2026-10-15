@props([
    'status' => [],
])

@php
    $status = array_merge([
        'totalBreached' => 0,
        'nearBreach' => 0,
        'unassigned' => 0,
        'noUpdate' => 0,
        'resolved' => 0,
        'paused' => 0,
        'totalVisits' => 0,
    ], $status ?? []);
@endphp

<div {{ $attributes->merge([
    'class' => 'w-full rounded-xl border border-slate-200
                bg-white p-3 shadow-sm',
]) }}>

    {{-- Compact header --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Case Status Overview
            </h2>
            <p class="text-xs text-slate-500">
                SLA, ownership and case activity
            </p>
        </div>
    </div>

    {{-- Compact filter row --}}
    <div class="mb-3 flex gap-2 overflow-x-auto pb-1">

        <x-casemanagement::form-elements.button-generic
            label="All Open"
            action="allOpenCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Breached"
            bg="bg-red-50"
            text="text-red-700"
            hover="bg-red-100"
            action="allBreachedCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Near Breach"
            bg="bg-amber-50"
            text="text-amber-700"
            hover="bg-amber-100"
            action="allNearBreachCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Unassigned"
            bg="bg-blue-50"
            text="text-blue-700"
            hover="bg-blue-100"
            action="allUnassignedCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="No Update"
            bg="bg-violet-50"
            text="text-violet-700"
            hover="bg-violet-100"
            action="allNoUpdateCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Paused"
            bg="bg-zinc-100"
            text="text-zinc-700"
            hover="bg-zinc-200"
            action="allPausedCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Resolved"
            bg="bg-emerald-50"
            text="text-emerald-700"
            hover="bg-emerald-100"
            action="allResolvedCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Closed"
            bg="bg-slate-800"
            text="text-white"
            hover="bg-slate-700"
            action="allClosedCases"
        />

        <x-casemanagement::form-elements.button-generic
            label="Reset"
            bg="bg-white"
            text="text-slate-600"
            hover="bg-slate-50"
            action="resetFilterSelection"
        />
    </div>

    {{-- Compact status cards --}}
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3
                lg:grid-cols-4 xl:grid-cols-7">

        <x-casemanagement::page-elements.box-element
            title="SLA Breached"
            :status="$status['totalBreached']"
            label="Urgent"
            containerBg="bg-red-50"
            containerBorder="border-red-100"
            headerText="text-red-700"
            titleText="text-red-900"
            subTitleText="text-red-600"
        />

        <x-casemanagement::page-elements.box-element
            title="Near Breach"
            :status="$status['nearBreach']"
            label="Approaching limit"
            containerBg="bg-amber-50"
            containerBorder="border-amber-100"
            headerText="text-amber-700"
            titleText="text-amber-900"
            subTitleText="text-amber-700"
        />

        <x-casemanagement::page-elements.box-element
            title="Unassigned"
            :status="$status['unassigned']"
            label="No owner"
            containerBg="bg-blue-50"
            containerBorder="border-blue-100"
            headerText="text-blue-700"
            titleText="text-blue-900"
            subTitleText="text-blue-600"
        />

        <x-casemanagement::page-elements.box-element
            title="No Update"
            :status="$status['noUpdate']"
            label="Review activity"
            containerBg="bg-violet-50"
            containerBorder="border-violet-100"
            headerText="text-violet-700"
            titleText="text-violet-900"
            subTitleText="text-violet-600"
        />

        <x-casemanagement::page-elements.box-element
            title="Paused"
            :status="$status['paused']"
            label="SLA paused"
            containerBg="bg-zinc-50"
            containerBorder="border-zinc-200"
            headerText="text-zinc-700"
            titleText="text-zinc-900"
            subTitleText="text-zinc-600"
        />

        <x-casemanagement::page-elements.box-element
            title="Resolved"
            :status="$status['resolved']"
            label="Resolved cases"
            containerBg="bg-emerald-50"
            containerBorder="border-emerald-100"
            headerText="text-emerald-700"
            titleText="text-emerald-900"
            subTitleText="text-emerald-700"
        />

        <x-casemanagement::page-elements.box-element
            title="Site Visits"
            :status="$status['totalVisits']"
            label="Scheduled"
            containerBg="bg-rose-50"
            containerBorder="border-rose-100"
            headerText="text-rose-700"
            titleText="text-rose-900"
            subTitleText="text-rose-600"
        />

    </div>
</div>
