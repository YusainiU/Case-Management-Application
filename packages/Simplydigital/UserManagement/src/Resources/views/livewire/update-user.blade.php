<div>
    <x-usermanagement::modal title="Update User Details">
            <div class="space-y-4">
                <div>
                    <x-usermanagement::input-label for="name" name="Name" />
                    <x-usermanagement::text-input id="name" type="text" wire:model.defer="name" />
                    <x-usermanagement::input-error field="name" class="mt-2" />
                </div>

                <div>
                    <x-usermanagement::input-label for="email" name="Email" />
                    <x-usermanagement::text-input id="email" type="email" wire:model.defer="email" />
                    <x-usermanagement::input-error field="email" class="mt-2" />
                </div>

                <div>
                    <x-usermanagement::input-label for="role" name="Role" />
                    <x-usermanagement::select id="role" name="role" model="role" :options="$roles" placeholder="Select a role" />
                </div>

                <div>
                    <x-usermanagement::checkbox id="active" name="status" value="1" label="Active" model="active" :checked="$active" />
                </div>
            </div>
    </x-usermanagement::modal>
</div>