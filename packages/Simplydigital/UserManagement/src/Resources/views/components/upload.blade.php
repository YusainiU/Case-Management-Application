<div class="pt-20 pb-10 px-6 text-center">
    <div class="bg-white shadow-2xl rounded-2xl p-8 transition-all duration-300 hover:shadow-indigo-200">

        <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">
            Upload Your File
        </h2>
        <p class="text-gray-500 text-center mb-6 text-sm">
            Drag & drop or click to browse
        </p>

        <form wire:submit.prevent='save' class="w-full max-w-md mx-auto">

            <div x-data="{ fileName: '' }">
                <label
                    class="group flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer transition-all duration-300 hover:border-indigo-500 hover:bg-indigo-50">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">

                        <!-- Icon -->
                        <x-usermanagement-ui-icon name="dragAndDrop" />

                        <p class="mb-2 text-sm text-gray-500 group-hover:text-indigo-600 transition">
                            <span class="font-semibold">Click to upload</span> or drag and
                            drop
                        </p>
                        <p class="text-xs text-gray-400">
                            PNG, JPG, PDF (Max 10MB)
                        </p>

                    </div>

                    <input 
                        id="fileInput" 
                        type="file" 
                        class="hidden" 
                        wire:model='image'
                        @change="fileName = $event.target.files[0]?.name" 
                    />

                </label>

                <!-- File name preview -->
                <p id="fileName" x-text="fileName" class="mt-4 text-sm text-center text-indigo-600 font-medium"></p>

            </div>

            <div class="mt-8 flex flex-col md:flex-row justify-center gap-4">
                <!-- Upload Button -->
                <button 
                    type="submit"
                    class="mt-6 w-full bg-linear-to-r from-indigo-500 to-purple-600 text-white py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                    Upload File
                </button>
                <button
                    class="mt-6 w-full bg-red-700 text-gray-100 py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300"
                    wire:click='removeProfileImage'>
                    Remove Photo
                </button>                
                <button
                    class="mt-6 w-full bg-gray-200 text-gray-700 py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300"
                    wire:click='closeUploadSection'>
                    Cancel
                </button>
            </div>

        </form>

    </div>
</div>
