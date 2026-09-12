@php
    $tdClass = Config::get('user-management.tableClasses.td');
    $thClass = Config::get('user-management.tableClasses.th');
    $trThClass = Config::get('user-management.tableClasses.colTr');
    $divTopClass = Config::get('user-management.tableClasses.divTop');
    $divSeparator = Config::get('user-management.tableClasses.divSeparator');
    $table = Config::get('user-management.tableClasses.table');
    $tdClass .= ' align-top';
@endphp

<div>

    <x-usermanagement::modal title="Permissions" wire:model="showModal" widthsize="6xl" cancelText="Close" confirm="confirm"
        confirmText="syncronize" cancelThisWith="closeForm" showConfirm="">


        <div class="mt-5">

            {{-- Introduction --}}
            <div class="rounded-md bg-blue-50 p-4">

                <div class="flex rounded-xl border border-gray-400 bg-white p-6 shadow-[4px_5px_12px_rgba(0,0,0,0.08)]">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800"> Package Permissions </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                The following permissions are provided by the installed Simplydigital packages.
                                Synchronising will ensure they are available in the permission system.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($lastPage > 1)
                    <div class="flex items-center justify-between mt-4">

                        <div class="text-sm text-gray-600">
                            Page {{ $currentPage }} of {{ $lastPage }}
                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-600">

                            {{-- Previous --}}
                            <button type="button" wire:click="previousPage" @disabled($currentPage <= 1)
                                class="px-3 py-2 text-sm border rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                                Previous
                            </button>

                            {{-- Next --}}
                            <button type="button" wire:click="nextPage" @disabled($currentPage >= $lastPage)
                                class="px-3 py-2 text-sm border rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                                Next
                            </button>

                        </div>

                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="{{ $table }}">
                        <thead class="">
                            <tr class="{{ $trThClass }}">
                                <th class="{{ $thClass }}">Name</th>
                                <th class="{{ $thClass }}">Description</th>
                                <th class="{{ $thClass }}">Guard</th>
                                <th class="{{ $thClass }}">Registered</th>
                                <th class="{{ $thClass }}">Database</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($permissions) > 0)
                                @php
                                    $groupedPermissions = $permissions->groupBy('group');
                                @endphp
                                @foreach ($groupedPermissions as $group => $groupPermissions)
                                    <tr>
                                        <td colspan="5" class="{{ $tdClass }}">
                                            <h4 class="text-sm font-semibold text-gray-800">
                                                {{ $group }}
                                            </h4>
                                        </td>
                                    </tr>
                                    @foreach ($groupPermissions as $permission)
                                        <tr class="hover:bg-gray-50">
                                            <td class="{{ $tdClass }}">{{ $permission['name'] }}</td>
                                            <td class="{{ $tdClass }}">{{ $permission['description'] }}</td>
                                            <td class="{{ $tdClass }}">{{ $permission['guard'] }}</td>
                                            <td class="{{ $tdClass }} text-center">
                                                @if ($permission['registered'])
                                                    <span class="text-green-600 font-semibold">✓</span>
                                                @endif
                                            </td>

                                            <td class="{{ $tdClass }} text-center">
                                                @if ($permission['database'])
                                                    <span class="text-green-600 font-semibold">✓</span>
                                                @else
                                                    <span class="text-red-600 font-semibold">✗</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="{{ $tdClass }}">
                                        No package permissions have been registered.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Information --}}
                <div
                    class="text-sm text-gray-500 rounded-xl border border-gray-400 bg-white p-6 shadow-[4px_5px_12px_rgba(0,0,0,0.08)] mt-5 ">
                    <p>
                        Existing permissions will not be duplicated. Permissions are defined by the packages and
                        synchronised with the application's permission database.
                    </p>
                </div>

            </div>

        </div>



        <button type="submit" wire:loading.attr="disabled" wire:click="confirm"
            class="ml-2 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2
           text-white transition-all duration-200
           hover:bg-indigo-700
           disabled:cursor-not-allowed disabled:opacity-70">
            {{-- Normal state --}}
            <span wire:loading.remove wire:target="confirm">
                Synchronise Permissions
            </span>

            {{-- Loading state --}}
            <span wire:loading wire:target="confirm" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                        class="opacity-25" />

                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span>Synchronising...</span>
            </span>
        </button>


    </x-usermanagement::modal>

</div>
