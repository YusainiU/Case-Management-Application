@php
    
    use Carbon\Carbon;
    
@endphp

<div class="grid grid-cols-1 gap-4 p-4">

    @if($displayCreateModal)
        <livewire:create-case :customerId="$customerId"/>
    @endif

    @if($displaySummaryModal)
        <livewire:case-summary :caseId="$caseId" />
    @endif

    @if($displayCustomerModal)
        <livewire:customer-summary :customerId="$customer->id" />
    @endif

    @if($displayDescription)
        <x-casemanagement::page-elements.modal-simple 
            title="{{ $descriptionDetails['name'] }}" 
            cancelThisWith="closeDescriptionModal"
        >
            <h4 class="font-bold mb-4">Case Description:</h4>
            {{ $descriptionDetails['description'] }}
        </x-casemanagement::page-elements.modal-simple>
    @endif

    @if ($displayProfileModal)
        <livewire:manage-case :case="$ticket" />
    @endif
        
    @if($displaySlaStatus)
        <livewire:case-sla :case="$ticket" />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title title="{{ $this->listTitle }} {{  $ui_params['title']  }}"
            description="Table List View" aside="what is this" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Create New Case" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />

    @if(!$hideStatus)
        <livewire:case-status />
    @endif
    
    {{ $cases?->links() }}

    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Case Number
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
                SLA
            </x-casemanagement::table-elements.table-th>            
            <x-casemanagement::table-elements.table-th>
                Status
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Actions
            </x-casemanagement::table-elements.table-th>            
        </thead>
        <tbody>
            @if($cases && $cases?->isNotEmpty())
                @foreach ($cases as $case)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-2 items-center justify-center">
                                <span>
                                    {{ $cases->firstItem() + $loop->index }}
                                </span>
                                @if($case->closed_ticket_id)
                                <x-casemanagement::page-elements.tooltip text="Closed Case">
                                    <a href="#" wire:click="openProfileModal({{ $case->closed_ticket_id }})">
                                        <x-casemanagement-ui-icon name="reopen" class="w-5 h-5"/>
                                    </a>
                                </x-casemanagement::page-elements.tooltip>                                    
                                @endif
                            </div>
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{  ltrim(strstr($case->number, '-'), '-') }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ Carbon::parse($case->created_at)->format('d/m/Y H:i') }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <x-casemanagement::page-elements.tooltip text="{{ $case->customer?->name }}">
                                <x-casemanagement::page-elements.href 
                                    label="{{ $case->customer?->name }}"
                                    action="openCustomerSummaryModal({{ $case->customer?->id }})" 
                                />                                
                            </x-casemanagement::page-elements.tooltip>                                                              
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <x-casemanagement::page-elements.tooltip text="View Case Description">
                                <x-casemanagement::page-elements.href 
                                    label="{{ Str::of($case->description)->limit(20) }}" 
                                    action="openDescription({{ $case->id }})"
                                />
                            </x-casemanagement::page-elements.tooltip>
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $case->assignedToUser?->name ?? '?'}}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td textcolor="{{ $case->slaTasks?->sla_flag }}">
                            <x-casemanagement::page-elements.tooltip text="View SLA Status">
                                <button 
                                    wire:click="openSlaStatus({{ $case->id }})" 
                                    class="hover:underline hover:text-blue-500 cursor-pointer"
                                >
                                    @if($case->closed_datetime)
                                        Closed
                                    @else
                                        <x-casemanagement::table-elements.table-td-content-sla :case="$case" />
                                    @endif
                                </button>
                            </x-casemanagement::page-elements.tooltip>
                        </x-casemanagement::table-elements.table-td>                        
                        <x-casemanagement::table-elements.table-td textcolor="{{ $case->state->color() }}">
                            {{ $case->state?->label() }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-4">
                                <x-casemanagement::form-elements.button-secondary label="Summary" action="openSummaryModal({{ $case->id }})" />
                                <x-casemanagement::form-elements.button-secondary label="Manage" action="openProfileModal({{ $case->id }})" />
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