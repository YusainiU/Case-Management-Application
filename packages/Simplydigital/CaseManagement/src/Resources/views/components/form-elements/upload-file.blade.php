<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
        <div>
            <h3 class="text-base font-semibold text-slate-900">
                Case Attachments
            </h3>
            <p class="mt-0.5 text-sm text-slate-500">
                Upload and manage files associated with this case.
            </p>
        </div>

        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
            {{ $this->attachments->count() }}
            {{ $this->attachments->count() === 1 ? 'file' : 'files' }}
        </span>
    </div>

    <div class="space-y-5 p-4 sm:p-5">

        {{-- Upload form --}}
        <form wire:submit="uploadAttachment" class="space-y-3">

            <label for="case-attachment"
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-indigo-400 hover:bg-indigo-50/50">

                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.6"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 16V4m0 0L8 8m4-4l4 4M4 16.5v2A1.5 1.5 0 005.5 20h13a1.5 1.5 0 001.5-1.5v-2" />
                    </svg>
                </span>

                <span class="mt-3 text-sm font-semibold text-slate-800">
                    Select a file to upload
                </span>

                <span class="mt-1 text-xs text-slate-500">
                    Choose a document or other permitted attachment from your device.
                </span>

                <span class="mt-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                    Browse files
                </span>

                <input
                    id="case-attachment"
                    type="file"
                    wire:model="attachment"
                    class="sr-only"
                    aria-describedby="attachment-help"
                />
            </label>

            <p id="attachment-help" class="text-xs text-slate-500">
                Please ensure the file complies with your application's upload restrictions.
            </p>

            {{-- Selected file / temporary upload status --}}
            <div wire:loading wire:target="attachment"
                class="flex items-center gap-2 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2.5 text-sm text-indigo-700">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Preparing file for upload...
            </div>

            @if ($errors->has('attachment'))
                <div role="alert"
                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ $errors->first('attachment') }}
                </div>
            @endif

            {{-- Upload action --}}
            <div class="flex justify-end">
                <x-casemanagement::form-elements.button-primary
                    type="submit"
                    label="Upload Attachment"
                    :loadingAttributeFalse="true"
                    loadingTarget="uploadAttachment"
                />
            </div>

        </form>

        {{-- Uploaded files --}}
        <div class="border-t border-slate-100 pt-4">

            <div class="mb-3">
                <h4 class="text-sm font-semibold text-slate-900">
                    Uploaded Files
                </h4>
                <p class="mt-0.5 text-xs text-slate-500">
                    Files currently associated with this case.
                </p>
            </div>

            @forelse ($this->attachments as $attachment)
                <div wire:key="case-attachment-{{ $attachment->id }}"
                    class="flex flex-col gap-3 border-b border-slate-100 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">

                    {{-- File information --}}
                    <div class="flex min-w-0 items-start gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.6"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 2v6h6M8 13h8m-8 4h8" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="break-words text-sm font-semibold text-slate-800">
                                {{ $attachment->original_name }}
                            </p>

                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <span>
                                    {{ $attachment->created_at?->format('d M Y, H:i') ?? 'Date unavailable' }}
                                </span>

                                <span aria-hidden="true">·</span>

                                <span>
                                    {{ $attachment->uploader?->name ?? 'Unknown uploader' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- File actions --}}
                    <div class="flex shrink-0 items-center gap-2 sm:justify-end">

                        @can('file.create', $uploadedAttachment)
                            <x-casemanagement::form-elements.button-secondary
                                action="downloadAttachment({{ $attachment->id }})"
                                label="Download"
                            />

                            <x-casemanagement::form-elements.button-danger
                                action="removeAttachment({{ $attachment->id }})"
                                label="Remove"
                            />
                        @endcan

                    </div>
                </div>

            @empty
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 ring-1 ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14 2v6h6M8 13h8m-8 4h5" />
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-semibold text-slate-800">
                        No attachments yet
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Uploaded case documents will appear here.
                    </p>
                </div>
            @endforelse

        </div>
    </div>
</div>
