<div>
    <x-modal wire:model="showModal" submit="save">
        <x-slot name="title">
            Add New Permission
        </x-slot>

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Permission Name
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="form.name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="e.g., create_users"
                />
                @error('form.name')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea
                    id="description"
                    wire:model="form.description"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    rows="3"
                    placeholder="Permission description"
                ></textarea>
                @error('form.description')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button
                type="button"
                @click="show = false"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
            >
                Cancel
            </button>
            <button
                type="submit"
                wire:click="save"
                class="px-4 py-2 ml-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
            >
                Save Permission
            </button>
        </x-slot>
    </x-modal>
</div>