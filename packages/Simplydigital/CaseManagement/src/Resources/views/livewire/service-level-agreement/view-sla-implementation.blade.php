<div class="grid grid-cols-1 gap-4 p-4">

    @if ($displayCreateModal)
        <livewire:create-sla-implementation 
            :customerId="$customerId"
            @close-create-modal="closeCreateModal"
        />
    @endif

    @if($displaySLAProfileModal)
        <livewire:sla-profile 
            :slaId="$slaId" 
            @close-profile-modal="closeSLAProfileModal"
        />
    @endif

    @if($displayCustomerSummaryModal)
        <livewire:customer-summary 
            :customerId="$customerId"
            @close-summary-modal="closeProfileModal"
        />
    @endif
    
    @if($displayUpdateModal)
        <livewire:create-sla-implementation 
            :slapId="$slapId"
            @close-update-modal="closeUpdateModal" 
        />
    @endif

    @if($displayProfileModal)
        <livewire:sla-implementation-profile 
            :slapId="$slapId"
            @close-profile-modal="closeProfileModal"
        />
    @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title 
            title="{{ $ui_params['title'] }}"
            description="Service Level Management Application." 
            aside="" />
    </div>

    <x-casemanagement::page-elements.seperator margin="2" />
    <x-casemanagement::form-elements.button-primary label="Implement SLA" action="openCreateModal" />
    <x-casemanagement::form-elements.input-filter />
    
    {{ $slaps?->links() }}

    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th sortable field="name" direction="asc">
                Name
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Customer
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>                
                SLA
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Priority
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                &nbsp;
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbody>
            @if ($slaps->isNotEmpty())
                @foreach ($slaps as $slap)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $slaps->firstItem() + $loop->index }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $slap->name }}
                        </x-casemanagement::table-elements.table-td>                        
                        <x-casemanagement::table-elements.table-td>
                            <x-casemanagement::page-elements.href 
                                label="{{ $slap->customer->name }}"
                                action="openCustomerSummaryModal({{ $slap->customer_id }})"                                
                            />                               
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <x-casemanagement::page-elements.href 
                                label="{{ $slap->serviceLevelAgreement->name }}"
                                action="openSLAProfileModal({{ $slap->service_level_agreement_id }})"                                
                            />                            
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $slap->priority->label() }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            <div class="flex flex-row gap-4">
                                <x-casemanagement::form-elements.button-secondary label="View"
                                    action="openProfileModal({{ $slap->id }})" />
                                <x-casemanagement::form-elements.button-primary label="Edit"
                                    action="openUpdateModal({{ $slap->id }})" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @endif            
        </tbody>        

    </x-casemanagement::table-elements.table>

</div>
