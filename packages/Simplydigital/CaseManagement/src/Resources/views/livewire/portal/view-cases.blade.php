<div class="space-y-8">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Customer portal</p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Cases</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Cases raised by you and other authorised contacts in your organisation.</p>
        </div>
        <flux:button href="{{ route('portal.cases.create') }}" variant="primary" icon="plus" wire:navigate>Raise a case</flux:button>
    </section>

    <section class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter cases">
                @foreach (['open' => 'Open', 'closed' => 'Closed', 'all' => 'All cases'] as $filter => $label)
                    <flux:button
                        wire:key="case-filter-{{ $filter }}"
                        wire:click="$set('statusFilter', '{{ $filter }}')"
                        :variant="$statusFilter === $filter ? 'primary' : 'ghost'"
                        wire:loading.attr="disabled"
                    >
                        {{ $label }}
                    </flux:button>
                @endforeach
            </div>
            <flux:input
                wire:model.live.debounce.300ms="search"
                type="search"
                icon="magnifying-glass"
                placeholder="Search by title or case number"
                class="w-full sm:max-w-sm"
                aria-label="Search cases"
            />
        </div>

        <div wire:loading class="text-sm text-zinc-500 dark:text-zinc-400">Updating cases…</div>

        @if ($cases->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:icon name="inbox" class="mx-auto size-8 text-zinc-400" />
                <h2 class="mt-3 text-base font-semibold text-zinc-900 dark:text-white">No matching cases</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Try another status filter or search term.</p>
            </div>
        @else
            <div class="divide-y divide-zinc-200 overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                @foreach ($cases as $case)
                    <a wire:key="case-{{ $case->id }}" href="{{ route('portal.cases.show', ['case' => $case->id]) }}" class="flex flex-col gap-4 p-5 transition hover:bg-zinc-50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-zinc-800/60" wire:navigate>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $case->number }} <span aria-hidden="true">·</span> {{ $case->category->label() }}</p>
                            <h2 class="mt-1 truncate font-semibold text-zinc-950 dark:text-white">{{ $case->short_description }}</h2>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Last updated {{ $case->updated_at->diffForHumans() }}</p>
                        </div>
                        <x-casemanagement::portal.status-badge :state="$case->state" />
                    </a>
                @endforeach
            </div>

            <div>{{ $cases->links('livewire::tailwind') }}</div>
        @endif
    </section>
</div>
