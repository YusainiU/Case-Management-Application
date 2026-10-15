
@props([
    'title' => 'Modal Title',
    'description' => null,
    'widthsize' => 'md',
    'cancelThisWith' => 'closeThisModal',
    'confirm' => null,
    'cancelText' => 'Cancel',
    'confirmText' => 'Confirm',
    'showFooter' => true,
    'showConfirm' => true,
    'bgColor' => 'bg-white',
    'borderColor' => 'border-slate-200',
    'border' => 'border rounded-2xl shadow-2xl',
    'width' => 'w-full',
    'mx' => 'mx-0',
])

<div
    class="fixed inset-0 z-50 flex items-center justify-center
           overflow-y-auto bg-slate-950/60 p-3 backdrop-blur-sm
           sm:p-6"
    role="presentation"
>
    <section
        @class([
            "max-w-{$widthsize}",
            $bgColor,
            $borderColor,
            $border,
            $width,
            $mx,
            'flex max-h-[calc(100dvh-1.5rem)] min-h-0 flex-col overflow-hidden',
            'sm:max-h-[calc(100dvh-3rem)]',
        ])
        role="dialog"
        aria-modal="true"
        aria-label="{{ $title }}"
    >
        {{-- Header --}}
        <header
            class="flex shrink-0 items-start justify-between gap-4
                   border-b border-slate-200 px-5 py-5 sm:px-7"
        >
            <div class="min-w-0">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                    {{ $title }}
                </h2>

                @if ($description)
                    <p class="mt-1.5 text-sm leading-6 text-slate-500">
                        {{ $description }}
                    </p>
                @endif
            </div>

            <button
                type="button"
                wire:click="{{ $cancelThisWith }}"
                aria-label="Close modal"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center
                       rounded-lg text-slate-400 transition
                       hover:bg-slate-100 hover:text-slate-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </header>

        {{-- Scrollable body --}}
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain
                    px-5 py-5 sm:px-7 sm:py-6">
            {{ $slot }}
        </div>

        {{-- Optional footer --}}
        @if ($showFooter)
            <footer
                class="flex shrink-0 justify-end gap-3 border-t
                       border-slate-200 bg-slate-50/80 px-5 py-4
                       sm:px-7"
            >
                <button
                    type="button"
                    wire:click="{{ $cancelThisWith }}"
                    class="rounded-lg border border-slate-300 bg-white
                           px-4 py-2.5 text-sm font-medium text-slate-700
                           transition hover:bg-slate-50
                           focus:outline-none focus:ring-2
                           focus:ring-indigo-500"
                >
                    {{ $cancelText }}
                </button>

                @if ($showConfirm)
                    <button
                        type="button"
                        wire:click="{{ $confirm ?? 'confirm' }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5
                               text-sm font-semibold text-white shadow-sm
                               transition hover:bg-indigo-700
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ $confirmText }}
                    </button>
                @endif
            </footer>
        @endif
    </section>
</div>
