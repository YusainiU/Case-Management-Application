@php
    use Carbon\Carbon;
@endphp

<div class="space-y-6">

    {{-- Existing Livewire modals --}}
    @if ($displayCreateModal)
        <livewire:create-case :customerId="$customerId" @close-create-modal="closeCreateModal"
            @close-update-modal="closeUpdateModal" />
    @endif

    @if ($displaySummaryModal)
        <livewire:case-summary :caseId="$caseId" @close-summary-modal="closeSummaryModal" />
    @endif

    @if ($displayCustomerModal)
        <livewire:customer-summary :customerId="$customer->id" @close-summary-modal="closeSummaryModal" />
    @endif

    @if ($displayDescription)
        <x-casemanagement::page-elements.modal-simple title="{{ $descriptionDetails['name'] }}"
            cancelThisWith="closeDescriptionModal">
            <h4 class="mb-3 font-semibold text-gray-900 dark:text-white">
                Case Description
            </h4>

            <div class="whitespace-pre-wrap text-sm leading-6
                        text-gray-600 dark:text-gray-300">
                {{ $descriptionDetails['description'] }}
            </div>
        </x-casemanagement::page-elements.modal-simple>
    @endif

    @if ($displayProfileModal)
        <livewire:manage-case :case="$ticket" @close-profile-modal="closeProfileModal" />
    @endif

    @if ($displaySlaStatus)
        <livewire:case-sla :case="$ticket" @close-summary-modal="closeSummaryModal" />
    @endif


    {{-- Page heading --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center
                sm:justify-between">

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-10 items-center justify-center
                            rounded-xl bg-indigo-50 text-indigo-600
                            dark:bg-indigo-500/10 dark:text-indigo-400">
                    <flux:icon name="folder-open" class="size-5" />
                </div>

                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight
                               text-gray-950 dark:text-white">
                        {{ $this->listTitle }} {{ $ui_params['title'] }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage cases, monitor SLAs and track case progress.
                    </p>
                </div>
            </div>
        </div>

        <div class="shrink-0">
            <x-casemanagement::form-elements.button-primary label="Create New Case" action="openCreateModal" />
        </div>

    </div>


    {{-- Filters --}}
    <div
        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm
                dark:border-gray-800 dark:bg-gray-900">

        <div class="mb-3 flex items-center gap-2">
            <flux:icon name="adjustments-horizontal" class="size-4 text-gray-500" />

            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                Find cases
            </h2>
        </div>

        <div class="min-w-0">
            <x-casemanagement::form-elements.input-filter />
        </div>

        @if (!$hideStatus)
            <div class="mt-4 border-t border-gray-100 pt-4
                        dark:border-gray-800">
                <livewire:case-status />
            </div>
        @endif

    </div>


    {{-- Case table --}}
    <div
        class="overflow-hidden rounded-xl border border-gray-200
                bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

        {{-- Table heading --}}
        <div
            class="flex flex-col gap-2 border-b border-gray-200
                    px-5 py-4 sm:flex-row sm:items-center
                    sm:justify-between dark:border-gray-800">

            <div>
                <h2 class="text-base font-semibold text-gray-900
                           dark:text-white">
                    Case Records
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Review and manage your case records.
                </p>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                @if ($cases)
                    {{ $cases->total() }} records
                @endif
            </div>

        </div>


        {{-- Responsive table --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-left text-sm">

                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr>
                        <th scope="col"
                            class="w-16 px-4 py-3.5 text-center text-xs
                                   font-semibold uppercase tracking-wider
                                   text-gray-500 dark:text-gray-400">
                            #
                        </th>

                        <th scope="col"
                            class="px-4 py-3.5 text-xs font-semibold
                                   uppercase tracking-wider text-gray-500
                                   dark:text-gray-400">
                            Case Number
                        </th>

                        <th scope="col" wire:click="sortByField('created_at','asc')"
                            class="cursor-pointer whitespace-nowrap px-4
                                   py-3.5 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500
                                   hover:text-indigo-600 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                Created At
                                <flux:icon name="chevrons-up-down" class="size-3.5" />
                            </span>
                        </th>

                        <th scope="col" wire:click="sortByField('customer_id','asc')"
                            class="cursor-pointer px-4 py-3.5 text-xs
                                   font-semibold uppercase tracking-wider
                                   text-gray-500 hover:text-indigo-600
                                   dark:text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                Site
                                <flux:icon name="chevrons-up-down" class="size-3.5" />
                            </span>
                        </th>

                        <th scope="col" wire:click="sortByField('description','asc')"
                            class="cursor-pointer px-4 py-3.5 text-xs
                                   font-semibold uppercase tracking-wider
                                   text-gray-500 hover:text-indigo-600
                                   dark:text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                Summary
                                <flux:icon name="chevrons-up-down" class="size-3.5" />
                            </span>
                        </th>

                        <th scope="col"
                            class="px-4 py-3.5 text-xs font-semibold
                                   uppercase tracking-wider text-gray-500
                                   dark:text-gray-400">
                            Assigned To
                        </th>

                        <th scope="col"
                            class="px-4 py-3.5 text-xs font-semibold
                                   uppercase tracking-wider text-gray-500
                                   dark:text-gray-400">
                            SLA
                        </th>

                        <th scope="col"
                            class="px-4 py-3.5 text-xs font-semibold
                                   uppercase tracking-wider text-gray-500
                                   dark:text-gray-400">
                            Status
                        </th>

                        <th scope="col"
                            class="px-4 py-3.5 text-right text-xs
                                   font-semibold uppercase tracking-wider
                                   text-gray-500 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @if ($cases && $cases->isNotEmpty())

                        @foreach ($cases as $case)
                            <tr wire:key="case-row-{{ $case->id }}"
                                class="transition-colors hover:bg-gray-50/80
                                       dark:hover:bg-gray-800/40">

                                {{-- Row number --}}
                                <td
                                    class="px-4 py-4 text-center
                                           text-gray-400 dark:text-gray-500">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="tabular-nums">
                                            {{ $cases->firstItem() + $loop->index }}
                                        </span>

                                        @if ($case->closed_ticket_id)
                                            <x-casemanagement::page-elements.tooltip text="Closed Case">
                                                <button type="button"
                                                    wire:click="openProfileModal({{ $case->closed_ticket_id }})"
                                                    aria-label="View closed case"
                                                    class="rounded p-1 text-gray-400
                                                           hover:bg-indigo-50
                                                           hover:text-indigo-600
                                                           dark:hover:bg-gray-700">
                                                    <x-casemanagement-ui-icon name="reopen" class="size-4" />
                                                </button>
                                            </x-casemanagement::page-elements.tooltip>
                                        @endif
                                    </div>
                                </td>


                                {{-- Case number --}}
                                <td class="px-4 py-4">
                                    <span
                                        class="font-semibold text-gray-900
                                                 dark:text-white">
                                        {{ ltrim(strstr($case->number, '-'), '-') }}
                                    </span>
                                </td>


                                {{-- Created date --}}
                                <td
                                    class="whitespace-nowrap px-4 py-4
                                           text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="calendar-days" class="size-4 text-gray-400" />

                                        {{ Carbon::parse($case->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                </td>


                                {{-- Customer / site --}}
                                <td class="max-w-52 px-4 py-4">
                                    <x-casemanagement::page-elements.tooltip text="{{ $case->customer?->name }}">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex size-8 shrink-0
                                                        items-center justify-center
                                                        rounded-lg bg-gray-100
                                                        text-gray-600
                                                        dark:bg-gray-800
                                                        dark:text-gray-300">
                                                <flux:icon name="building-office-2" class="size-4" />
                                            </div>

                                            <div class="min-w-0">
                                                <div
                                                    class="truncate font-medium
                                                            text-gray-800
                                                            dark:text-gray-200">
                                                    <x-casemanagement::page-elements.href :label="$case->customer?->name ?? 'Unknown site'"
                                                        :action="'openCustomerSummaryModal(' .
                                                            ($case->customer?->id ?? '') .
                                                            ')'" />
                                                </div>
                                            </div>
                                        </div>
                                    </x-casemanagement::page-elements.tooltip>
                                </td>


                                {{-- Case summary --}}
                                <td class="max-w-64 px-4 py-4">
                                    <x-casemanagement::page-elements.tooltip text="View Case Description">
                                        <button type="button" wire:click="openDescription({{ $case->id }})"
                                            class="block max-w-64 truncate text-left
                                                   font-medium text-gray-600
                                                   transition-colors
                                                   hover:text-indigo-600
                                                   dark:text-gray-300
                                                   dark:hover:text-indigo-400">
                                            {{ Str::of($case->description)->limit(40) }}
                                        </button>
                                    </x-casemanagement::page-elements.tooltip>
                                </td>


                                {{-- Assigned user --}}
                                <td class="px-4 py-4">
                                    @if ($case->assignedToUser)
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="flex size-8 shrink-0
                                                        items-center justify-center
                                                        rounded-full bg-indigo-50
                                                        text-xs font-semibold
                                                        text-indigo-700
                                                        dark:bg-indigo-500/10
                                                        dark:text-indigo-300">
                                                {{ strtoupper(substr($case->assignedToUser->name, 0, 1)) }}
                                            </div>

                                            <span
                                                class="whitespace-nowrap
                                                         font-medium text-gray-700
                                                         dark:text-gray-300">
                                                {{ $case->assignedToUser->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5
                                                     rounded-full bg-amber-50
                                                     px-2.5 py-1 text-xs
                                                     font-medium text-amber-700
                                                     dark:bg-amber-500/10
                                                     dark:text-amber-400">
                                            <span
                                                class="size-1.5 rounded-full
                                                         bg-amber-500"></span>
                                            Unassigned
                                        </span>
                                    @endif
                                </td>


                                {{-- SLA --}}
                                <td class="px-4 py-4">
                                    <x-casemanagement::page-elements.tooltip text="View SLA Status">
                                        <button type="button" wire:click="openSlaStatus({{ $case->id }})"
                                            class="inline-flex items-center gap-1.5
                                                   rounded-md px-2 py-1 text-left
                                                   text-sm font-medium
                                                   transition-colors
                                                   hover:bg-gray-100
                                                   hover:text-indigo-600
                                                   dark:hover:bg-gray-800
                                                   dark:hover:text-indigo-400
                                                   {{ $case->slaTasks?->sla_flag }}">
                                            <flux:icon name="clock" class="size-4 shrink-0" />

                                            @if ($case->closed_datetime)
                                                Closed
                                            @else
                                                <x-casemanagement::table-elements.table-td-content-sla
                                                    :case="$case" />
                                            @endif
                                        </button>
                                    </x-casemanagement::page-elements.tooltip>
                                </td>


                                {{-- Status --}}
                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5
                                                 whitespace-nowrap rounded-full
                                                 bg-gray-100 px-2.5 py-1
                                                 text-xs font-medium
                                                 text-gray-700
                                                 dark:bg-gray-800
                                                 dark:text-gray-300
                                                 {{ $case->state?->color() }}">
                                        <span
                                            class="size-1.5 rounded-full
                                                     bg-current"></span>
                                        {{ $case->state?->label() ?? 'Unknown' }}
                                    </span>
                                </td>


                                {{-- Actions --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" wire:click="openSummaryModal({{ $case->id }})"
                                            class="inline-flex items-center gap-1.5
                                                   rounded-lg border border-gray-200
                                                   bg-white px-3 py-2 text-xs
                                                   font-medium text-gray-700
                                                   transition-colors hover:bg-gray-50
                                                   hover:text-indigo-600
                                                   focus-visible:outline-2
                                                   focus-visible:outline-offset-2
                                                   focus-visible:outline-indigo-600
                                                   dark:border-gray-700
                                                   dark:bg-gray-900
                                                   dark:text-gray-300
                                                   dark:hover:bg-gray-800">
                                            <flux:icon name="eye" class="size-3.5" />
                                            Summary
                                        </button>

                                        <button type="button" wire:click="openProfileModal({{ $case->id }})"
                                            class="inline-flex items-center gap-1.5
                                                   rounded-lg bg-indigo-600 px-3 py-2
                                                   text-xs font-medium text-white
                                                   transition-colors hover:bg-indigo-700
                                                   focus-visible:outline-2
                                                   focus-visible:outline-offset-2
                                                   focus-visible:outline-indigo-600
                                                   dark:bg-indigo-500
                                                   dark:hover:bg-indigo-400">
                                            <flux:icon name="pencil-square" class="size-3.5" />
                                            Manage
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div
                                    class="mx-auto flex size-12 items-center
                                            justify-center rounded-xl bg-gray-100
                                            text-gray-500 dark:bg-gray-800
                                            dark:text-gray-400">
                                    <flux:icon name="folder-open" class="size-6" />
                                </div>

                                <h3
                                    class="mt-4 text-sm font-semibold
                                           text-gray-900 dark:text-white">
                                    No cases found
                                </h3>

                                <p
                                    class="mt-1 text-sm text-gray-500
                                          dark:text-gray-400">
                                    Try adjusting your filters or create a new case.
                                </p>
                            </td>
                        </tr>

                    @endif

                </tbody>

            </table>
        </div>


        {{-- Pagination --}}
        @if ($cases)
            <div
                class="flex flex-col gap-3 border-t border-gray-200
                        px-5 py-4 sm:flex-row sm:items-center
                        sm:justify-between dark:border-gray-800">

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if ($cases->total() > 0)
                        Showing
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $cases->firstItem() }}–{{ $cases->lastItem() }}
                        </span>
                        of
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $cases->total() }}
                        </span>
                        cases
                    @else
                        No records to display
                    @endif
                </p>

                <div>
                    {{ $cases->links() }}
                </div>

            </div>
        @endif

    </div>

</div>
