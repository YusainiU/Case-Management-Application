
@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $table = Config::get('user-management.tableClasses.table');
@endphp

<div class="mx-auto max-w-7xl space-y-6 bg-slate-50/70 px-4 py-8 text-slate-800 sm:px-6 lg:px-8">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 ring-1 ring-indigo-100">
                <x-usermanagement-ui-icon
                    name="shield-check"
                    class="h-6 w-6 text-indigo-600"
                />
            </div>

            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                    Permissions
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage and review system access permissions.
                </p>
            </div>
        </div>
    </div>

    <x-usermanagement::modal
        title="Permissions"
        wire:model="showModal"
        widthsize="6xl"
        cancelText="Close"
        confirm="{{ $confirmCall }}"
        confirmText="{{ $confirmText }}"
        cancelThisWith="closeThis"
    >
        @if ($showList)
            <div class="space-y-5">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-slate-900">
                            All permissions
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Browse registered system access rules.
                        </p>
                    </div>

                    <div class="w-full sm:max-w-sm">
                        <label for="permission-search" class="sr-only">
                            Search permissions
                        </label>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <x-usermanagement-ui-icon
                                    name="search"
                                    class="h-5 w-5 text-slate-400"
                                />
                            </div>

                            <x-usermanagement::text-input
                                id="permission-search"
                                name="filter"
                                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                                placeholder="Search permissions..."
                                wire:model.live="filter"
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th scope="col" class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        ID
                                    </th>
                                    <th scope="col" class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Permission name
                                    </th>
                                    <th scope="col" class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Guard
                                    </th>
                                    <th scope="col" class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Created at
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($permissions as $permission)
                                    <tr wire:key="permission-{{ $permission->id }}"
                                        class="transition-colors duration-150 hover:bg-slate-50/80">

                                        <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-slate-500">
                                            {{ $permission->id }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="text-sm font-medium text-slate-900">
                                                {{ $permission->name }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                                {{ $permission->guard_name }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">
                                            {{ $permission->created_at?->format('d M Y, H:i') ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100">
                                                <x-usermanagement-ui-icon
                                                    name="shield-check"
                                                    class="h-6 w-6 text-slate-400"
                                                />
                                            </div>

                                            <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                                No permissions found
                                            </h3>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Try changing your search or filter.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-white px-5 py-4">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        @endif

        @if ($showAddPermission)
            <div class="space-y-6">
                <button
                    type="button"
                    wire:click="backToList"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"
                >
                    <x-usermanagement-ui-icon
                        name="left"
                        class="h-4 w-4"
                    />
                    Back to permissions
                </button>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">
                            Create permission
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Define a unique permission identifier for your application.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <x-usermanagement::input-label
                            for="name"
                            name="Permission name"
                            class="block text-sm font-medium text-slate-700"
                            :required="true"
                        />

                        <x-usermanagement::text-input
                            type="text"
                            id="name"
                            wire:model.defer="name"
                            placeholder="e.g. create_users"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                        />

                        <p class="text-xs text-slate-500">
                            Use descriptive identifiers such as create_users or edit_users.
                        </p>

                        <x-usermanagement::input-error
                            field="name"
                            class="mt-2"
                        />
                    </div>
                </div>
            </div>
        @endif
    </x-usermanagement::modal>
</div>
