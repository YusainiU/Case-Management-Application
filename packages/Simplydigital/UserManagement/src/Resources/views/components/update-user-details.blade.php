<div class="pt-20 pb-10 px-6 text-center">

    <div class="bg-white shadow-2xl rounded-2xl p-8 transition-all duration-300 hover:shadow-indigo-200">

        <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">
            Update User Details
        </h2>

        <form wire:submit.prevent='saveDetails'>

            <div class="flex flex-col md:flex-row gap-6 justify-center">
                <div class="mb-4 flex-row gap-4 justify-end">
                    <x-usermanagement::input-label name="Address 1" />
                    <x-usermanagement::text-input model="address_1" />
                </div>
                <div class="mb-4 flex-row gap-4">
                    <x-usermanagement::input-label name="Address 2" />
                    <x-usermanagement::text-input model="address_2" />
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-6 justify-center">
                <div class="mb-4 flex-row gap-4">
                    <x-usermanagement::input-label name="County" />
                    <x-usermanagement::text-input model="county" />
                </div>
                <div class="mb-4 flex-row gap-4">
                    <x-usermanagement::input-label name="Postcode" />
                    <x-usermanagement::text-input model="postcode" />
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-6 justify-center">
                <div class="mb-4 flex-row gap-4">
                    <x-usermanagement::input-label name="Country" />
                    <x-usermanagement::text-input model="country" />
                </div>
            </div>

            <div class="mt-8 flex flex-col md:flex-row justify-center gap-4">
                <!-- Upload Button -->
                <button type="submit"
                    class="mt-6 w-full bg-linear-to-r from-indigo-500 to-purple-600 text-white py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                    Update Details
                </button>
                <button
                    class="mt-6 w-full bg-gray-200 text-gray-700 py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300"
                    wire:click='resetAll'>
                    Cancel
                </button>
            </div>

        </form>
    </div>
</div>
