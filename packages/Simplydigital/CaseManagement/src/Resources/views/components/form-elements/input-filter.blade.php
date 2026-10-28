    <div     
        {{ $attributes->merge([
            'class' => 'mt-3 mb-3 flex justify-between gap-x-6 rounded-xl border shadow-sm transition',
        ]) }}
    >
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>    