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

    <x-casemanagement::page-elements.page-header-section section="Service Level Agreement Management Application"
        title="{{ $ui_params['title'] }}" description="Service Level Management Application." :showCreateButton="true"
        createButtonLabel="Create New SLA Application" createAction="openCreateModal" showBackButton="true" />    


    <section class="">

        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($slaps)
                <div class="text-sm text-slate-500">
                    @if ($slaps->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $slaps->firstItem() }}–{{ $slaps->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $slaps->total() }}
                        </span>
                        Service Level Agreement Applications
                    @else
                        No service level agreement application found
                    @endif
                </div>
            @endif
        </div>               

        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($slaps?->hasPages())
                <div class="text-slate-100!">
                    {{ $slaps->links('livewire::tailwind') }}
                </div>
            @endif
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
                                <x-casemanagement::form-elements.button-edit action="openProfileModal({{ $slap->id }})" label="View" />
                                <x-casemanagement::form-elements.button-view label="Edit" action="openUpdateModal({{ $slap->id }})" />
                            </div>
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @endif            
        </tbody>        

    </x-casemanagement::table-elements.table>

    </section>

</div>
