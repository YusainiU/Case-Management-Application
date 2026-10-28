<x-casemanagement::page-elements.outer-wrapper>


    @if ($displayCreateModal)
        <livewire:create-sla />
    @endif

    @if ($displayProfileModal)
        <livewire:sla-profile :slaId="$slaId">
    @endif

    @if ($displayUpdateModal)
        <livewire:create-sla :slaId="$slaId" />
    @endif

    <x-casemanagement::page-elements.page-header-section section="Service Level Agreement Management"
        title="{{ $ui_params['title'] }}" description="Manage your service level agreement here." :showCreateButton="true"
        createButtonLabel="Create New SLA" createAction="openCreateModal" showBackButton="true" />

    <section class="">
        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($slas)
                <div class="text-sm text-slate-500">
                    @if ($slas->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $slas->firstItem() }}–{{ $slas->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $slas->total() }}
                        </span>
                        Service Level Agreement
                    @else
                        No service level agreement found
                    @endif
                </div>
            @endif
        </div>        

        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter
                    class="rounded-xl border shadow-sm transition border-slate-500" />
            </div>
            <x-casemanagement::page-elements.pagination :paginator="$slas" /> 
        </div>

        <x-casemanagement::table-elements.table>
            <thead>
                <x-casemanagement::table-elements.table-th>
                    &nbsp;
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th sortable field="name" direction="asc">
                    Name
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    Start Day
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    End Day
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    Start At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    End At
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    Response Time
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    Fixed Time
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    Include Public Holiday
                </x-casemanagement::table-elements.table-th>
                <x-casemanagement::table-elements.table-th>
                    &nbsp;
                </x-casemanagement::table-elements.table-th>
            </thead>
            <tbody>
                @if ($slas->isNotEmpty())
                    @foreach ($slas as $sla)
                        <tr>
                            <x-casemanagement::table-elements.table-td>
                                {{ $slas->firstItem() + $loop->index }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->name }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->start_day->label() }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->end_day->label() }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->getServiceStartTime() }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->getServiceEndTime() }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->getResponseTime() }} hours
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->getFixedTime() }} hours
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                {{ $sla->include_public_holiday ? 'Yes' : 'No' }}
                            </x-casemanagement::table-elements.table-td>
                            <x-casemanagement::table-elements.table-td>
                                <div class="flex flex-row gap-4">
                                    <x-casemanagement::form-elements.button-edit action="openProfileModal({{ $sla->id }})" label="View" />
                                    <x-casemanagement::form-elements.button-view label="Edit" action="openUpdateModal({{ $sla->id }})" />
                                </div>
                            </x-casemanagement::table-elements.table-td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </x-casemanagement::table-elements.table>

        {{-- Bottom pagination --}}
        <div class="px-5 py-4 sm:px-6">
            <x-casemanagement::page-elements.pagination :paginator="$slas" />
        </div>        

    </section>

</x-casemanagement::page-elements.outer-wrapper>
