<div>
    <x-modal wire:model="showModal" submit="createRole" title="Add New Role">
        <div class="space-y-4">
            <div>
                <label for="roleName" class="block text-sm font-medium text-gray-700">
                    Role Name
                </label>
                <input
                    type="text"
                    id="roleName"
                    wire:model="roleName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Enter role name"
                    required
                />
                @error('roleName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="roleDescription" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea
                    id="roleDescription"
                    wire:model="roleDescription"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Enter role description"
                    rows="3"
                ></textarea>
                @error('roleDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Permissions
                </label>
                <div class="space-y-2">
                    @foreach ($availablePermissions as $permission)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="selectedPermissions"
                                value="{{ $permission->id }}"
                                class="rounded border-gray-300"
                            />
                            <span class="ml-2 text-sm text-gray-600">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedPermissions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button
                type="button"
                wire:click="$toggle('showModal')"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="px-4 py-2 ml-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
            >
                Create Role
            </button>
        </x-slot>
    </x-modal>
</div>