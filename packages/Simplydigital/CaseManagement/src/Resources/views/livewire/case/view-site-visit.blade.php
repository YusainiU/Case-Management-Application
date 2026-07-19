<div class="grid grid-cols-1 gap-4 p-4">

    @if($this->message)
        <x-casemanagement::page-elements.toast-alert-simple 
            header="Changes Made"
            mode="information"
            message="{{ $message }}"
        />
    @endif       

    @if($this->displayCreateModal)
        <livewire:create-site-visit :modal="true" :case="$case" />
    @endif

    @if($this->displayProfileModal)
        <livewire:create-site-visit :modal="true" :taskSiteVisit="$selectedVisit" />
}   @endif

    <div class="my-4">
        <x-casemanagement::page-elements.section-title 
            title="{{ $ui_params['title'] }}"
            description="{{ $ui_params['description'] }}" 
            aside="what is this" 
        />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />

    @if($case && !$case->closed_datetime)
        <x-casemanagement::form-elements.button-primary label="Schedule a Visit" action="scheduleVisit" />
    @endif

    {{ $siteVisits?->links() }}

    <x-casemanagement::table-elements.table>
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
        <tbody>
            @if($siteVisits)
                @foreach($siteVisits as $visit)
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
                            @if($case && !$case->closed_datetime)
                            <div class="flex flex-row gap-4">
                                @php
                                    $completed = $visit->offsite_at !== null;
                                    $button = "secondary";
                                    $label = "Manage";
                                    if($completed) {
                                        $button = "primary";
                                        $label = "Completed";
                                    }
                                @endphp
                                <x-dynamic-component
                                    :component="'casemanagement::form-elements.button-'.$button" 
                                    label="{{ $label }}" 
                                    action="manageVisit({{ $visit->id }})" 
                                />
                            </div>
                            @else
                                Case Closed
                            @endif
                        </x-casemanagement::table-elements.table-td>                        
                    </tr>
                @endforeach
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>   

</div>