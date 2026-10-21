
<div>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center
               overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="profile-modal-title"
    >
        <div
            class="my-auto w-full max-w-{{ $widthsize ?? '2xl' }}
                   overflow-hidden rounded-2xl border border-gray-200
                   bg-white shadow-2xl shadow-slate-900/10"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b
                        border-gray-100 px-6 py-5 sm:px-8">
                <div>
                    <h2
                        id="profile-modal-title"
                        class="text-xl font-semibold tracking-tight
                               text-gray-900"
                    >
                        User Profile
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage your personal information and preferences.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="{{ $cancelThisWith ?? 'closeModal' }}"
                    aria-label="Close modal"
                    class="inline-flex h-9 w-9 items-center justify-center
                           rounded-lg text-gray-400 transition
                           hover:bg-gray-100 hover:text-gray-700
                           focus:outline-none focus:ring-2
                           focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 6l12 12M18 6L6 18"
                        />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="max-h-[70vh] overflow-y-auto
                        bg-white px-6 py-6 sm:px-8 sm:py-8">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if ($showFooter ?? true)
                <div class="flex items-center justify-end gap-3
                            border-t border-gray-100 bg-gray-50/70
                            px-6 py-4 sm:px-8">
                    <button
                        type="button"
                        wire:click="{{ $cancelThisWith ?? 'closeModal' }}"
                        class="inline-flex items-center justify-center
                               rounded-lg border border-gray-200
                               bg-white px-4 py-2.5 text-sm font-medium
                               text-gray-700 shadow-sm transition
                               hover:bg-gray-50 hover:border-gray-300
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ $cancelText ?? 'Close' }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
