<div class="space-y-8">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Customer portal</p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Welcome, {{ auth()->user()->name }}</h1>
            <p class="max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">Track requests raised by your organisation and keep your support conversations in one place.</p>
        </div>

        <flux:button href="{{ route('portal.cases.create') }}" variant="primary" icon="plus" wire:navigate>
            Raise a case
        </flux:button>
    </section>

    <section aria-label="Case totals" class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('portal.cases.index') }}" class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-700" wire:navigate>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Open cases</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $openCount }}</p>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Requests currently being worked on</p>
        </a>
        <a href="{{ route('portal.cases.index') }}" class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-700" wire:navigate>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Completed or closed</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $closedCount }}</p>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Previous requests from your organisation</p>
        </a>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recently updated</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">The latest activity across your organisation's cases.</p>
            </div>
            <a href="{{ route('portal.cases.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" wire:navigate>All cases</a>
        </div>

        @if ($recentCases->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="inbox" class="mx-auto size-8 text-zinc-400" />
                <h3 class="mt-3 text-base font-semibold text-zinc-900 dark:text-white">No cases yet</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Raise a case when your organisation needs support.</p>
                <flux:button class="mt-5" href="{{ route('portal.cases.create') }}" variant="primary" wire:navigate>Raise a case</flux:button>
            </div>
        @else
            <div class="divide-y divide-zinc-200 overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                @foreach ($recentCases as $case)
                    <a wire:key="recent-case-{{ $case->id }}" href="{{ route('portal.cases.show', ['case' => $case->id]) }}" class="flex flex-col gap-3 p-5 transition hover:bg-zinc-50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-zinc-800/60" wire:navigate>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $case->number }}</p>
                            <h3 class="mt-1 truncate font-semibold text-zinc-950 dark:text-white">{{ $case->short_description }}</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Updated {{ $case->updated_at->diffForHumans() }}</p>
                        </div>
                        <x-casemanagement::portal.status-badge :state="$case->state" />
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
