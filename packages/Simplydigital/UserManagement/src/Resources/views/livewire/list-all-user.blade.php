<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    {{-- Create User Modal --}}
    @if ($showCreateModal)
        <livewire:addnewuser-component @close-create-new-user="closeCreateNewUser" />
    @endif

    {{-- Update User Modal --}}
    @if ($showUpdateModal)
        <livewire:updateuser-component :userId="$userId" @user-updated="userUpdated" />
    @endif

    {{-- Permissions Modal --}}
    @if ($showPermissions)
        <livewire:addnewpermission-component @close-permission-modal="closePermissionModal" />
    @endif

    {{-- Roles Modal --}}
    @if ($showRoles)
        <livewire:listroles-component @close-role-modal="closeRoleModal" />
    @endif

    {{-- User Profile Modal --}}
    @if ($showUserProfile)
        <livewire:userprofile-component :userId="$userId" @close-user-profile="closeUserProfile" />
    @endif

    {{-- Page Header --}}
    <x-usermanagement::section-title>
        <x-slot name="title">
            <div class="flex items-center gap-3 text-slate-800">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-usermanagement-ui-icon name="user" class="h-7 w-7" />
                </div>
                <span class="text-2xl font-bold tracking-tight sm:text-3xl">
                    User Management
                </span>
            </div>
        </x-slot>

        <x-slot name="description">
            Manage users, assign roles, and control access to your application.
        </x-slot>
    </x-usermanagement::section-title>

    <x-usermanagement::section-action-buttons :actions="[
        [
            'label' => 'Roles',
            'icon' => 'user',
            'wire' => 'openRoles',
        ],
        [
            'label' => 'Permissions',
            'icon' => 'shield',
            'wire' => 'openPermissions',
        ],
        [
            'label' => 'Create New User',
            'icon' => 'user-plus',
            'wire' => 'openCreateUser',
            'class' => '!bg-violet-600 !text-white hover:!bg-violet-700 whitespace-nowrap',
            'align' => true,
        ],
    ]" />

    <x-usermanagement::section-search label="Search users" title="Users" description="Search and manage registered users." />

    {{-- Pagination: Top --}}
    
    <div class="flex flex-col">
        {{ $users?->links() }}
    </div>
    

    {{-- Users Table --}}
    <x-usermanagement::table>

        <x-usermanagement::table-th :items="[
            'User',
            'Email Address',
            'Roles',
            'Actions',
        ]" />

        <x-usermanagement::table-body >

            @forelse ($users as $user)
                @php
                    $roles = $user->getRoleNames();
                @endphp

                <tr wire:key="user-row-{{ $user->id }}" class="transition hover:bg-slate-50/80">

                    {{-- User Name --}}
                    <x-usermanagement::table-td>
                        <button type="button" wire:click="openUserProfile({{ $user->id }})"
                            class="group inline-flex items-center gap-3 text-left focus:outline-none">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-50 text-sm font-bold uppercase text-violet-700 ring-1 ring-inset ring-violet-100">
                                {{ \Illuminate\Support\Str::substr($user->name, 0, 1) }}
                            </span>

                            <span>
                                <span class="block font-semibold text-slate-800 transition group-hover:text-violet-700">
                                    {{ $user->name }}
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-500">
                                    View profile
                                </span>
                            </span>
                        </button>
                    </x-usermanagement::table-td>

                    {{-- Email --}}
                    <x-usermanagement::table-td>
                        <span class="break-all text-sm text-slate-600">
                            {{ $user->email }}
                        </span>
                    </x-usermanagement::table-td>

                    {{-- Roles --}}
                     <x-usermanagement::table-td>
                        @if ($roles->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($roles as $role)
                                    <span
                                        class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-100">
                                        {{ $role }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span
                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                No roles assigned
                            </span>
                        @endif
                    </x-usermanagement::table-td>

                    {{-- Actions --}}
                    <x-usermanagement::table-td>
                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                            <x-usermanagement::button-update action="openUpdateUser({{ $user->id }})" label="Update Roles" />
                            <x-usermanagement::button-delete  label="Delete" recordId="{{ $user->id }}" />    
                        </div>
                    </x-usermanagement::table-td>

                </tr>
            @empty
                <x-usermanagement::table-tr-nodata />
            @endforelse
            
        </x-usermanagement::table-body>

    </x-usermanagement::table>

        {{-- Pagination: Bottom --}}
        <div class="flex justify-end">
            {{ $users?->links() }}
        </div>

</div>
