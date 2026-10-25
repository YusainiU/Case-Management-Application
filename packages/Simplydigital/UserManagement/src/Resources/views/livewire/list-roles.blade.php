@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $table = Config::get('user-management.tableClasses.table');

    $tdClass .= ' align-top px-4 py-3 text-sm text-gray-600';
    $thClass .= ' px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500';

    $groupPermissions = static function ($permissions) {
        return collect($permissions)->groupBy(static function ($permission) {
            $name = is_string($permission) ? $permission : $permission->name;
            $resource = \Illuminate\Support\Str::before($name, '.');

            return $resource !== '' ? $resource : 'other';
        });
    };
@endphp

<div class="text-gray-800">
    <x-usermanagement::modal
        title="Roles"
        wire:model="showModal"
        widthsize="6xl"
        cancelText="Close"
        confirm="{{ $confirmCall }}"
        confirmText="{{ $confirmText }}"
        cancelThisWith="closeThis"
        showFooter="false"
    >

        @if ($showList)
            <div class="mt-2 flex flex-col gap-4">

                {{-- Search --}}
                <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <div class="w-full max-w-sm">
                        <x-usermanagement::text-input
                            name="filter"
                            class="w-full bg-white text-gray-800 placeholder:text-gray-400"
                            placeholder="Search roles..."
                            wire:model.live="filter"
                        />
                    </div>

                    <span class="hidden text-sm text-gray-500 sm:inline">
                        Manage roles and permissions
                    </span>
                </div>

                {{-- Roles table --}}
                <div class="max-h-[55vh] overflow-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="{{ $table }} w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr class="{{ $trThClass }}">
                                <th class="{{ $thClass }}">ID</th>
                                <th class="{{ $thClass }}">Name</th>
                                <th class="{{ $thClass }}">Guard Name</th>
                                <th class="{{ $thClass }}">Permissions</th>
                                <th class="{{ $thClass }}">Created At</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($roles as $role)
                                <tr class="transition-colors hover:bg-indigo-50/40">
                                    <td class="{{ $tdClass }}">
                                        <span class="text-gray-500">{{ $role->id }}</span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <button
                                            type="button"
                                            wire:click="displayUpdateForm('{{ $role->name }}')"
                                            class="font-medium text-indigo-600 transition hover:text-indigo-800 hover:underline"
                                        >
                                            {{ $role->name }}
                                        </button>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                            {{ $role->guard_name }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        @forelse ($groupPermissions($role->permissions) as $permissionGroup => $permissions)
                                            <div class="mb-2 last:mb-0" wire:key="role-{{ $role->id }}-permission-group-{{ $permissionGroup }}">
                                                <p class="mb-1 text-xs font-semibold text-gray-500">
                                                    {{ \Illuminate\Support\Str::headline($permissionGroup) }}
                                                </p>
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($permissions as $permi)
                                                        <span class="inline-flex rounded-md border border-indigo-100 bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">
                                                            {{ \Illuminate\Support\Str::after($permi->name, '.') }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-xs italic text-gray-400">
                                                No permissions
                                            </span>
                                        @endforelse
                                    </td>

                                    <td class="{{ $tdClass }} whitespace-nowrap">
                                        {{ $role->created_at->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                                <x-usermanagement-ui-icon name="left" class="h-5 w-5" />
                                            </span>
                                            <p class="text-sm font-medium text-gray-700">
                                                No roles found
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Try changing your search.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="border-t border-gray-100 pt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        @endif

        @if ($showAddRole)
            <div class="max-h-[65vh] space-y-5 overflow-y-auto pr-1">
                <div class="flex items-center">
                    <button
                        type="button"
                        wire:click="backToList"
                        class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    >
                        <x-usermanagement-ui-icon name="left" class="h-4 w-4" />
                        Back to roles
                    </button>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">
                        Create role
                    </h3>

                    <div>
                        <x-usermanagement::input-label
                            for="name"
                            name="Name"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                            :required="true"
                        />

                        <x-usermanagement::text-input
                            type="text"
                            id="name"
                            wire:model.defer="name"
                            placeholder="e.g., create_users"
                            class="w-full"
                        />

                        <x-usermanagement::input-error field="name" class="mt-2" />
                    </div>

                    <div class="mt-5">
                        <x-usermanagement::input-label
                            for="Permissions"
                            name="Permissions"
                            class="block text-sm font-medium text-gray-700"
                            :required="true"
                        />

                        <div class="mt-3 space-y-4">
                            @foreach ($groupPermissions($allPermissions) as $permissionGroup => $permissions)
                                <div wire:key="create-permission-group-{{ $permissionGroup }}">
                                    <h4 class="mb-2 text-sm font-semibold text-gray-700">
                                        {{ \Illuminate\Support\Str::headline($permissionGroup) }}
                                    </h4>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        @foreach ($permissions as $permissionId => $permissionName)
                                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition hover:border-indigo-200 hover:bg-indigo-50/50" wire:key="create-permission-{{ $permissionId }}">
                                                <x-usermanagement::checkbox
                                                    name="selectedPermissions"
                                                    value="{{ $permissionName }}"
                                                    model="selectedPermissions"
                                                    label="{{ \Illuminate\Support\Str::after($permissionName, '.') }}"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <x-usermanagement::input-error field="selectedPermissions" class="mt-2" />
                    </div>
                </div>
            </div>
        @endif

        @if ($showUpdateRole)
            <div class="max-h-[65vh] space-y-5 overflow-y-auto pr-1">
                <div class="flex items-center">
                    <button
                        type="button"
                        wire:click="backToList"
                        class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    >
                        <x-usermanagement-ui-icon name="left" class="h-4 w-4" />
                        Back to roles
                    </button>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="mb-5 border-b border-gray-100 pb-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                            Editing role
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $selectedRole->name }}
                        </h3>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <x-usermanagement::input-label
                            for="Permissions"
                            name="Permissions"
                            class="block text-sm font-medium text-gray-700"
                            :required="true"
                        />
                        <button
                            type="button"
                            wire:click="selectAllPermissions"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-800"
                        >
                            Select all permissions
                        </button>
                    </div>

                    <div class="mt-3 space-y-4">
                        @foreach ($groupPermissions($allPermissions) as $permissionGroup => $permissions)
                            <div wire:key="edit-permission-group-{{ $permissionGroup }}">
                                <h4 class="mb-2 text-sm font-semibold text-gray-700">
                                    {{ \Illuminate\Support\Str::headline($permissionGroup) }}
                                </h4>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    @foreach ($permissions as $permissionId => $permissionName)
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition hover:border-indigo-200 hover:bg-indigo-50/50" wire:key="edit-permission-{{ $permissionId }}">
                                            <x-usermanagement::checkbox
                                                name="selectedPermissions"
                                                value="{{ $permissionName }}"
                                                model="selectedPermissions"
                                                label="{{ \Illuminate\Support\Str::after($permissionName, '.') }}"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-usermanagement::input-error field="selectedPermissions" class="mt-2" />
                </div>
            </div>
        @endif
    </x-usermanagement::modal>
</div>
