@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $divTopClass = Config::get('user-management.tableClasses.divTop');
    $divSeparator = Config::get('user-management.tableClasses.divSeparator');
    $table = Config::get('user-management.tableClasses.table');

    $tdClass .= ' align-top';

@endphp

<div class="text-gray-800">

    <x-usermanagement::modal title="Roles" wire:model="showModal" widthsize="6xl" cancelText="Close"
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
                            <th class="{{ $thClass }}">Permissions</th>
                            <th class="{{ $thClass }}">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="{{ $tdClass }}">{{ $role->id }}</td>
                                <td class="{{ $tdClass }}">
                                    <a href="#" wire:click="displayUpdateForm('{{ $role->name }}')" class="hover:underline">
                                        {{ $role->name }}
                                    </a>
                                </td>
                                <td class="{{ $tdClass }}">{{ $role->guard_name }}</td>
                                <td class="{{ $tdClass }}">
                                    @if($role->permissions->isNotEmpty())
                                        @foreach($role->permissions as $permi)
                                            <span class="block">{{ $permi->name }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="{{ $tdClass }}">
                                    {{ $role->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="{{ $tdClass }} text-center">
                                    No roles found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $roles->links() }}
            </div>

        @endif

        @if ($showAddRole)
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

                <div>
                    <x-usermanagement::input-label for="Permissions" name="Permissions" class="block text-sm font-medium text-gray-700" :required="true" />
                    <div class="space-y- mt-3">
                        @foreach ($allPermissions as $permissionId => $permissionName)
                            <x-usermanagement::checkbox 
                                name="selectedPermissions"
                                value="{{ $permissionName }}"
                                model="selectedPermissions"
                                label="{{ $permissionName }}"
                            />
                        @endforeach
                    </div>
                    <x-usermanagement::input-error field="selectedPermissions" class="mt-2" />
                </div>
            </div>

        @endif

        @if($showUpdateRole)
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
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $selectedRole->name }}
                    </h3>
                </div>

                <div>
                    <x-usermanagement::input-label for="Permissions" name="Permissions" class="block text-sm font-medium text-gray-700" :required="true" />
                    <div class="space-y- mt-3">
                        @foreach ($allPermissions as $permissionId => $permissionName)
                            <x-usermanagement::checkbox 
                                name="selectedPermissions"
                                value="{{ $permissionName }}"
                                model="selectedPermissions"
                                label="{{ $permissionName }}"
                            />
                        @endforeach
                    </div>
                    <x-usermanagement::input-error field="selectedPermissions" class="mt-2" />
                </div>                
            </div>            
        @endif

    </x-usermanagement::modal>

</div>
