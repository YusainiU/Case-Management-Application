
@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $table = Config::get('user-management.tableClasses.table');

    $tdClass .= ' align-top';
@endphp

<div class="mx-auto max-w-7xl bg-slate-50/70 px-4 py-8 text-slate-800 sm:px-6 lg:px-8">

    <x-usermanagement::modal
        title="User Management"
        wire:model="showModal"
        widthsize="6xl"
        cancelText="Close"
        confirm="confirm"
        confirmText="Synchronise"
        cancelThisWith="closeForm"
        showConfirm=""
    >
        <div class="mt-2 max-h-[65vh] space-y-4 overflow-y-auto overscroll-contain pr-2 sm:max-h-[70vh]">

            {{-- Page introduction --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-start gap-4 p-4 sm:p-5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 ring-1 ring-indigo-100">
                        <flux:icon
                            name="shield-check"
                            class="h-6 w-6 text-indigo-600"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold tracking-tight text-slate-900">
                                Package Permissions
                            </h2>

                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-100">
                                Package registry
                            </span>
                        </div>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                            Review permissions supplied by installed Simplydigital packages.
                            Synchronising ensures that package-defined permissions are available
                            in your application's permission system.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-3 sm:px-6">
                    <div class="flex items-start gap-2.5">
                        <x-usermanagement-ui-icon
                            name="info"
                            class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                        />

                        <p class="text-xs leading-5 text-slate-500">
                            Existing permissions are preserved and will not be duplicated.
                            Package definitions are synchronised with the application database.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            @if ($lastPage > 1)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">

                    <div class="text-sm text-slate-500">
                        Page
                        <span class="font-semibold text-slate-900">{{ $currentPage }}</span>
                        of
                        <span class="font-semibold text-slate-900">{{ $lastPage }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="previousPage"
                            @disabled($currentPage <= 1)
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <x-usermanagement-ui-icon
                                name="left"
                                class="h-4 w-4"
                            />
                            Previous
                        </button>

                        <button
                            type="button"
                            wire:click="nextPage"
                            @disabled($currentPage >= $lastPage)
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                            <x-usermanagement-ui-icon
                                name="right"
                                class="h-4 w-4"
                            />
                        </button>
                    </div>
                </div>
            @endif

            {{-- Permissions table --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex flex-col gap-1 border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h3 class="text-base font-semibold text-slate-900">
                        Permission registry
                    </h3>

                    <p class="text-sm text-slate-500">
                        Review each package permission and its current registration status.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">

                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Permission
                                </th>

                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Description
                                </th>

                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Guard
                                </th>

                                <th scope="col"
                                    class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Registered
                                </th>

                                <th scope="col"
                                    class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Database
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">

                            @if (count($permissions) > 0)

                                @php
                                    $groupedPermissions = $permissions->groupBy('group');
                                @endphp

                                @foreach ($groupedPermissions as $group => $groupPermissions)

                                    {{-- Group heading --}}
                                    <tr wire:key="permission-group-{{ $loop->index }}">
                                        <td colspan="5" class="bg-slate-50/80 px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50">
                                                    <flux:icon
                                                        name="tag"
                                                        class="h-4 w-4 text-indigo-600"
                                                    />
                                                </div>

                                                <h4 class="text-sm font-semibold text-slate-800">
                                                    {{ $group ?: 'General' }}
                                                </h4>

                                                <span class="ml-auto rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-200">
                                                    {{ $groupPermissions->count() }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach ($groupPermissions as $permission)

                                        <tr
                                            wire:key="package-permission-{{ $loop->parent->index }}-{{ $loop->index }}"
                                            class="transition-colors duration-150 hover:bg-slate-50/70"
                                        >
                                            {{-- Permission name --}}
                                            <td class="whitespace-nowrap px-4 py-3 align-top">
                                                <span class="text-sm font-medium text-slate-900">
                                                    {{ $permission['name'] }}
                                                </span>
                                            </td>

                                            {{-- Description --}}
                                            <td class="min-w-52 px-4 py-3 align-top">
                                                <p class="max-w-sm text-sm leading-5 text-slate-500">
                                                    {{ $permission['description'] ?: 'No description available' }}
                                                </p>
                                            </td>

                                            {{-- Guard --}}
                                            <td class="whitespace-nowrap px-4 py-3 align-top">
                                                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                                    {{ $permission['guard'] }}
                                                </span>
                                            </td>

                                            {{-- Registered status --}}
                                            <td class="whitespace-nowrap px-5 py-4 text-center align-top">
                                                @if ($permission['registered'])
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                        Registered
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Database status --}}
                                            <td class="whitespace-nowrap px-5 py-4 text-center align-top">
                                                @if ($permission['database'])
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                        <x-usermanagement-ui-icon
                                                            name="check"
                                                            class="h-3.5 w-3.5"
                                                        />
                                                        Present
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-200">
                                                        <x-usermanagement-ui-icon
                                                            name="x"
                                                            class="h-3.5 w-3.5"
                                                        />
                                                        Missing
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                    @endforeach
                                @endforeach

                            @else

                                {{-- Empty state --}}
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100">
                                            <x-usermanagement-ui-icon
                                                name="shield-check"
                                                class="h-6 w-6 text-slate-400"
                                            />
                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                            No package permissions found
                                        </h3>

                                        <p class="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                                            No package permissions have been registered.
                                            Check your installed packages and try again.
                                        </p>
                                    </td>
                                </tr>

                            @endif

                        </tbody>
                    </table>
                </div>

                {{-- Table footer --}}
                @if (count($permissions) > 0)
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500">
                            Permission status is based on the current application registry.
                        </p>

                        <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                            <x-usermanagement-ui-icon
                                name="database"
                                class="h-4 w-4 text-slate-400"
                            />
                            Database verification
                        </div>
                    </div>
                @endif

            </div>

            {{-- Synchronisation information --}}
            <div class="flex items-start gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white ring-1 ring-indigo-100">
                    <flux:icon
                        name="information-circle"
                        class="h-5 w-5 text-indigo-600"
                    />
                </div>

                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-slate-900">
                        Before you synchronise
                    </h4>

                    <p class="text-sm leading-6 text-slate-600">
                        Synchronisation adds missing package-defined permissions to the
                        application's permission database. Existing permissions will not
                        be duplicated.
                    </p>
                </div>
            </div>

            {{-- Synchronisation action --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-end">

                <button
                    type="button"
                    wire:click="closeForm"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200"
                >
                    Close
                </button>

                <button
                    type="button"
                    wire:loading.attr="disabled"
                    wire:click="confirm"
                    wire:target="confirm"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{-- Normal state --}}
                    <span wire:loading.remove wire:target="confirm" class="inline-flex items-center gap-2">
                        <x-usermanagement-ui-icon
                            name="refresh-cw"
                            class="h-4 w-4"
                        />
                        Synchronise Permissions
                    </span>

                    {{-- Loading state --}}
                    <span wire:loading wire:target="confirm" class="inline-flex items-center gap-2">
                        <svg
                            class="h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="3"
                                class="opacity-25"
                            />
                            <path
                                d="M12 2a10 10 0 0 1 10 10"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>

                        Synchronising...
                    </span>
                </button>
            </div>

        </div>
    </x-usermanagement::modal>

</div>
