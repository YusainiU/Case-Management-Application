@php

    $enableCreate = true;
    if ($case) {
        if ($case->closed_datetime) {
            $enableCreate = false;
        }
    }

@endphp

<div class="grid grid-cols-1 gap-4 p-4">

    @if ($this->message)
        <x-casemanagement::page-elements.toast-alert-simple header="Changes Made" mode="information"
            message="{{ $message }}" />
    @endif

    @if ($this->displayCreateModal)
        <livewire:create-site-visit :modal="true" :case="$case" />
    @endif

    @if ($this->displayProfileModal)
        <livewire:create-site-visit :modal="true" :taskSiteVisit="$selectedVisit" />
        }
    @endif

    <x-casemanagement::page-elements.page-header-section section="Site Visit Management" title="{{ $ui_params['title'] }}"
        description="{{ $ui_params['description'] }}" :showCreateButton="$enableCreate" createButtonLabel="Create New Visit"
        createAction="scheduleVisit" showBackButton="true" />


    <section class="">

        {{-- Directory header --}}
        <div
            class="flex flex-col gap-3 border-b border-slate-200
                   px-5 py-4 sm:flex-row sm:items-center
                   sm:justify-between sm:px-6">

            @if ($siteVisits)
                <div class="text-sm text-slate-500">
                    @if ($siteVisits->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $siteVisits->firstItem() }}–{{ $siteVisits->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $siteVisits->total() }}
                        </span>
                        Site Visits
                    @else
                        No site visits records found
                    @endif
                </div>
            @endif
        </div>

        {{-- Filters and pagination --}}
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($siteVisits?->hasPages())
                <div>
                    {{ $siteVisits->links('livewire::tailwind') }}
                </div>
            @endif
        </div>

        <x-casemanagement::table-elements.table mt="mt-0" mb="mb-0" px="px-0" py="py-0"
            border="border-0" whitespace="whitespace-nowrap">
            <thead>
                <x-casemanagement::table-elements.table-th sortable field="created_at" direction="asc">
                    Created At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="customer_id" direction="asc">
                    Site
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="visit_scheduled_at" direction="asc">
                    Scheduled At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="scheduled_by" direction="asc">
                    Scheduled By
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="enroute_at" direction="asc">
                    Enroute At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="onsite_at" direction="asc">
                    Onsite At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="offsite_at" direction="asc">
                    Offsite At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    &nbsp;
                </x-casemanagement::table-elements.table-th>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @if ($siteVisits)
                    @foreach ($siteVisits as $visit)
                        <tr>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->created_at->format('d-m-Y H:i') }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->ticket->customer->name }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->visit_scheduled_at?->format('d-m-Y H:i') }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->scheduledBy?->name }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->enroute_at?->format('d-m-Y H:i') }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->onsite_at?->format('d-m-Y H:i') }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $visit->offsite_at?->format('d-m-Y H:i') }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                @if ($visit->ticket && !$visit->ticket?->closed_datetime)
                                    <div class="flex flex-row gap-4">
                                        @if ($visit->offsite_at !== null)
                                            <x-casemanagement::form-elements.button-completed
                                                action="manageVisit({{ $visit->id }})" label="Completed" />
                                        @else
                                            <x-casemanagement::form-elements.button-manage
                                                action="manageVisit({{ $visit->id }})" label="Manage" />
                                        @endif
                                    </div>
                                @else
                                    <x-casemanagement::page-elements.info-label :enabled="false" label="Closed" />
                                @endif
                            </x-casemanagement::table-elements.table-td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </x-casemanagement::table-elements.table>

    </section>

</div>
