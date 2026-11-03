    <div     
        {{ $attributes->merge([
            'class' => 'mt-3 mb-3 flex justify-between gap-x-6 rounded-xl border shadow-sm transition dark:border-slate-700 dark:bg-slate-900',
        ]) }}
    >
        <x-input
            name="filter"
            class="min-w-0 bg-transparent text-slate-900 placeholder:text-slate-400 dark:bg-transparent dark:text-slate-100 dark:placeholder:text-slate-500"
            placeholder="Search records"
            wire:model.live="filter"
        />
    </div>    