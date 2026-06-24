<div class="grid grid-cols-1 gap-4 p-4">
    <div class="my-4">
        <x-casemanagement::page-elements.section-title 
            title="{{ $ui_params['title'] }}"
            description="Tracking Field Changes" 
            aside="what is this" 
        />
    </div>
    <x-casemanagement::page-elements.seperator margin="2" />
    {{ $activities?->links() }}
    <x-casemanagement::table-elements.table>
        <thead>
            <x-casemanagement::table-elements.table-th>
                Date
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Table
            </x-casemanagement::table-elements.table-th>            
            <x-casemanagement::table-elements.table-th>
                User
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Event
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Field
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                Old Value
            </x-casemanagement::table-elements.table-th>
            <x-casemanagement::table-elements.table-th>
                New Value
            </x-casemanagement::table-elements.table-th>
        </thead>
        <tbody>
            @if ($activities?->isNotEmpty())
                @foreach ($activities as $key => $activity)
                    <tr>
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['date'] }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['table'] }}
                        </x-casemanagement::table-elements.table-td>                        
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['user'] }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['event'] }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['field'] }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            {{ $activity['old'] }}
                        </x-casemanagement::table-elements.table-td>
                        <x-casemanagement::table-elements.table-td>
                            @php
                                $new = $activity['new'];
                                if(is_array($new)){
                                    $new = $new[$activity['field']];
                                }
                            @endphp
                            {{ $new }}
                        </x-casemanagement::table-elements.table-td>                                                                                                                        
                    </tr>
                @endforeach
            @endif
        </tbody>
    </x-casemanagement::table-elements.table>

</div>
