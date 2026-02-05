<div>
    <x-usermanagement::modal title="Add New User" wire:model="showModal">
        <!-- Name -->
        <div class="mb-4">
            <x-usermanagement::input-label for="name" name='Name' />
            <x-usermanagement::text-input id="name" class="block mt-1 w-full" type="text" model="name" required
                autofocus />
            <x-usermanagement::input-error field="name" class="mt-2" />

        </div>

        <!-- Email -->
        <div class="mb-4">
            <x-usermanagement::input-label for="email" name='Email' />
            <x-usermanagement::text-input id="email" class="block mt-1 w-full" type="email" wire:model="email"
                required />
            <x-usermanagement::input-error field="email" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-usermanagement::input-label for="password" name='Password' />
            <x-usermanagement::text-input id="password" class="block mt-1 w-full" type="password" wire:model="password"
                required />
            <x-usermanagement::input-error field="password" class="mt-2" />
        </div>

    </x-usermanagement::modal>
</div>
