<div class="space-y-4">

    {{-- File upload --}}
    <div>
        <label
            for="file-upload"
            class="block text-sm font-medium text-gray-700"
        >
            Upload file
        </label>

        <div class="mt-2">
            <input
                id="file-upload"
                type="file"
                wire:model="file"
                class="block w-full text-sm text-gray-900
                       border border-gray-300 rounded-lg
                       cursor-pointer bg-gray-50
                       focus:outline-none"
            >
        </div>

        {{-- Upload validation/loading --}}
        <div wire:loading wire:target="file" class="mt-2">
            <p class="text-sm text-gray-500">
                Uploading file...
            </p>
        </div>

        {{-- Validation error --}}
        @error('file')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>


    {{-- Selected file information --}}
    @if ($file)

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $file->getClientOriginalName() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ number_format($file->getSize() / 1024, 1) }} KB
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="$set('file', null)"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Remove
                </button>

            </div>

        </div>

    @endif


    {{-- Upload button --}}
    <div>
        <button
            type="button"
            wire:click="upload"
            wire:loading.attr="disabled"
            wire:target="upload"
            @disabled(!$file)
            class="inline-flex items-center px-4 py-2
                   text-sm font-medium text-white
                   bg-blue-600 rounded-lg
                   hover:bg-blue-700
                   focus:ring-4 focus:ring-blue-300
                   disabled:opacity-50
                   disabled:cursor-not-allowed"
        >

            <span wire:loading.remove wire:target="upload">
                Upload file
            </span>

            <span wire:loading wire:target="upload">
                Uploading...
            </span>

        </button>
    </div>


    {{-- Upload success message --}}
    @if (session()->has('file-upload-success'))

        <div
            class="rounded-lg bg-green-50 p-4 text-sm text-green-700"
        >
            {{ session('file-upload-success') }}
        </div>

    @endif

</div>