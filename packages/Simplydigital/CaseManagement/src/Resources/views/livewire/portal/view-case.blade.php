<div class="space-y-8">
    <a href="{{ route('portal.cases.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" wire:navigate>
        <flux:icon name="arrow-left" class="size-4" />
        Back to cases
    </a>

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $case->number }} <span aria-hidden="true">·</span> {{ $case->category->label() }}</p>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $case->short_description }}</h1>
            </div>
            <x-casemanagement::portal.status-badge :state="$case->state" />
        </div>

        <dl class="mt-6 grid gap-4 border-t border-zinc-200 pt-6 sm:grid-cols-2 dark:border-zinc-800">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Created</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $case->created_at->format('M j, Y \a\t g:i a') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Customer reference</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $case->customer_reference ?: '—' }}</dd>
            </div>
        </dl>

        <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-800">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Description</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $case->description }}</p>
        </div>
    </section>

    <livewire:portal-add-case-comment :case-id="$case->id" :key="'case-comments-'.$case->id" />
</div>
