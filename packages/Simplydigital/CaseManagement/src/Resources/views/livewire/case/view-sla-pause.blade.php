@php
    use Carbon\CarbonInterval;
@endphp

<div class="grid grid-cols-1 gap-4 p-4">

    <x-casemanagement::page-elements.page-header-section 
        section="Pauses" 
        title="{{ $ui_params['title'] }}"
        description="Pauses to the Service Level Agreement" 
        :showCreateButton="false" 
        showBackButton="true" />
    
     <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                Paused At
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Resumed At
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                SLA Remaining
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbod>
            @if($pauses->isNotEmpty())
                @foreach($pauses as $pause)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $pause->paused_at->format('d-m-Y H:i:s') }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $pause->resumed_at?->format('d-m-Y H:i:s') }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $pause->remaining ? CarbonInterval::seconds($pause->remaining)->cascade()->forHumans(): null }}
                        </x-casemanagement::table-elements.table-td>
                    </tr>
                @endforeach
            @endif
        </tbod>
     </x-casemanagement::table-elements.table>
</div>