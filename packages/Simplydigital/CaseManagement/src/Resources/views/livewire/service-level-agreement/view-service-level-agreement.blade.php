<div class="grid grid-cols-1 gap-4 p-4">

    @if ($displayCreateModal)
        <livewire:create-sla />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $ui_params['title'] }}"
            description="Manage Service Level Agreement." aside="what is this" />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New SLA" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    {{ $slas?->links() }}
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
                            {{ $sla->response_time }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $sla->fixed_time }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $sla->include_public_holiday ? 'Yes' : 'No' }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-4">
                                <x-casemanagement::form-elements.button-secondary label="View"
                                    action="openProfileModal({{ $sla->id }})" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>

</div>
