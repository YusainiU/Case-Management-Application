<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Roles</h2>
        <button wire:click="$toggle('showModal')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add Role
        </button>
    </div>
    
    <div class="mb-4">
        <h2 class="text-2xl font-bold">Roles</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Guard Name</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $role->id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $role->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $role->guard_name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $role->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
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
</div>