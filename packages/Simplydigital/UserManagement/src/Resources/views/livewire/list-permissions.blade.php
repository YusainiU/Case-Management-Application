@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $divTopClass = Config::get('user-management.tableClasses.divTop');
    $divSeparator = Config::get('user-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('user-management.buttonClasses.btnGrey');
    $tdActionLinkDelete = Config::get('user-management.buttonClasses.btnRed');
    $table = Config::get('user-management.tableClasses.table');
@endphp

<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <x-usermanagement::modal title="Permissions" wire:model="showModal" widthsize="6xl" cancelText="Close"
        confirm="{{ $confirmCall }}" confirmText="{{ $confirmText }}" cancelThisWith="closeThis">

        @if ($showList)

            <div class="mt-3 mb-3 flex justify-between gap-x-6">
                <x-usermanagement::text-input name="filter" class="text-gray-800" placeholder="Search records"
                    wire:model.live='filter' />
            </div>

            <div class="overflow-x-auto">
                <table class="{{ $table }}">
                    <thead class="">
                        <tr class="{{ $trThClass }}">
                            <th class="{{ $thClass }}">ID</th>
                            <th class="{{ $thClass }}">Name</th>
                            <th class="{{ $thClass }}">Guard Name</th>
                            <th class="{{ $thClass }}">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr class="hover:bg-gray-50">
                                <td class="{{ $tdClass }}">{{ $permission->id }}</td>
                                <td class="{{ $tdClass }}">{{ $permission->name }}</td>
                                <td class="{{ $tdClass }}">{{ $permission->guard_name }}</td>
                                <td class="{{ $tdClass }}">
                                    {{ $permission->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="{{ $tdClass }} text-center">
                                    No permissions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $permissions->links() }}
            </div>

        @endif

        @if ($showAddPermission)
            <div class="space-y-4">
                <div class="group">
                    <span class="cursor-pointer group-hover:hidden">
                        <x-usermanagement-ui-icon name="left" class="h-6 w-6 text-gray-900" />
                    </span>
                    <button class="hidden group-hover:block text-sm text-gray-800 cursor-pointer" wire:click="backToList">
                        Back to List
                    </button>
                </div>                
                <div>

                    <x-usermanagement::input-label for="name" name="Name" class="block text-sm font-medium text-gray-700" :required="true" />

                    <x-usermanagement::text-input
                        type="text" 
                        id="name"
                        wire:model.defer="name"                        
                        placeholder="e.g., create_users" 
                    />
                    <x-usermanagement::input-error field="name" class="mt-2" />
                </div>
            </div>
        @endif

    </x-usermanagement::modal>
    
</div>
