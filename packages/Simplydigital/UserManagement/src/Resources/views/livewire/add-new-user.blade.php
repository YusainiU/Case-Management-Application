<div class="container mx-auto p-6">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold mb-6">Add New User</h2>

        <form wire:submit.prevent="save">
            <!-- Name Field -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Name</label>
                <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter full name">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter email address">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" wire:model="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter password">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Role Field -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Role</label>
                <select wire:model="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600">
                    Save User
                </button>
                <button type="button" class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>