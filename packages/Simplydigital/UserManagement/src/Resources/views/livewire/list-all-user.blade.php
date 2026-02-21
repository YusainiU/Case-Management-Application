<div class="container mx-auto px-4 py-8">

    @if($showCreateModal)
        <livewire:addnewuser-component />
    @endif

    @if($showUpdateModal)
        <livewire:updateuser-component :userId="$userId" />
    @endif

    @if($showPermissions)
        <livewire:listpermissions-component />
    @endif

    @if($showRoles)
        <livewire:listroles-component />
    @endif

    @if($showUserProfile)
        <livewire:userprofile-component :userId="$userId" />
    @endif

    <x-usermanagement::section-title>
        <x-slot name="title">
            <div class="">
                <x-usermanagement-ui-icon name="user" class="h-10 w-10" />
                User Management                
            </div>
        </x-slot>
        <x-slot name="description">            
            Manage your users effectively.
        </x-slot>
    </x-usermanagement::section-title>

    <div class="flex justify-between items-center mt-4 mb-4">
        <button wire:click="openRoles" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Roles
        </button>
        <button wire:click="openPermissions" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Permissions
        </button>                
        <button wire:click="openCreateUser" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New User
        </button>
    </div>
    
    <div class="mt-3 mb-3 flex justify-between gap-x-6">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>
    
    <div class="mt-6">
        {{ $users->links() }}
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-top">
                            <a href="#" wire:click='openUserProfile({{ $user->id }})' class="hover:underline">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 align-top">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 align-top">
                            @php
                                $buildList = false;
                                $uroles = $user->getRoleNames();
                                if(!empty($uroles)){
                                    $buildList = true;
                                }
                            @endphp
                            @if($buildList)
                                @foreach($uroles as $urole)
                                    <span class="block">{{ $urole }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium align-top">
                            <a href="#" class="text-blue-600 hover:text-blue-900" wire:click="openUpdateUser({{ $user->id }})">Edit</a>
                            <a 
                                x-data
                                href="#" 
                                class="ml-4 text-red-600 hover:text-red-900"
                                @click="if (confirm('Delete this record?')) $wire.delete({{ $user->id }})"
                            >
                                Delete
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-600">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

</div>