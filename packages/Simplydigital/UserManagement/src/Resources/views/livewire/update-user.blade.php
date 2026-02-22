<div>
    <x-usermanagement::modal title="Update User Roles" cancelThisWith="closeThis" widthsize="4xl">
            <div class="space-y-4">
                <div>
                    <x-usermanagement::input-label for="role" name="Role" />
                    <x-usermanagement::select id="role" name="role" model="selectedRoles" :options="$roles" placeholder="Select a role" multiple="multiple" />
                </div>
            </div>
    </x-usermanagement::modal>
</div>