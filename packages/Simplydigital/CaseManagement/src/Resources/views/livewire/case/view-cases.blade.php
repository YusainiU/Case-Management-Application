@php
    
    use Simplydigital\CaseManagement\Enums\CaseState;
    use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
    
@endphp

<div class="grid grid-cols-1 gap-4 p-4">

    @if($displayCreateModal)
        <livewire:create-case />
    @endif

    @if($displaySummaryModal)
        <livewire:case-summary :caseId="$caseId" />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $ui_params['title'] }}"
            description="Cases Management: List" aside="what is this" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Case" action="openCreateModal" />
    {{-- <x-casemanagement::form-elements.button-primary label="Open Cases" action="openTab('{{ CaseState::Open }}')" /> --}}
    <x-casemanagement::form-elements.input-filter />


    <x-casemanagement::page-elements.case-status-panel-large />


     {{ $cases?->links() }}

    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="created_at" direction="asc">
                Created At
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="customer_id" direction="asc">
                Site
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="description" direction="asc">
                Summary
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Assigned To
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Status
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Actions
            </x-casemanagement::table-elements.table-th>            
        </thead>
        <tbody>
            @if($cases?->isNotEmpty())
                @foreach ($cases as $case)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $cases->firstItem() + $loop->index }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ CaseManagementUtilityServices::reverseYMDToDMY($case->created_at) }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $case->customer?->name }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ Str::of($case->description)->limit(30) }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $case->assignedToUser?->name }}
                        </x-casemanagement::table-elements.table-td>                        
                        <x-casemanagement::table-elements.table-td textcolor="{{ $case->state->color() }}">
                            {{ $case->state?->label() }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-4">
                                <x-casemanagement::form-elements.button-secondary label="View Summary" action="openSummaryModal({{ $case->id }})" />
                                <x-casemanagement::form-elements.button-secondary label="View Profile" action="openProfileModal({{ $case->id }})" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <x-casemanagement::table-elements.table-td colspan="6" class="text-center">
                        No cases found.
                    </x-casemanagement::table-elements.table-td>
                </tr>
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>     

</div>