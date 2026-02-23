@php
    $modalMain = Config::get('engineer-management.modalClasses.modalMain');
    $modalBackdrop = Config::get('engineer-management.modalClasses.modalBackdrop');
    $modalPanel = Config::get('engineer-management.modalClasses.modalPanel');
    $modalHeaderPanel = Config::get('engineer-management.modalClasses.modalHeaderPanel');
    $modalHeader = Config::get('engineer-management.modalClasses.modalHeader');
    $modalXButton = Config::get('engineer-management.modalClasses.modalXButton');
    $modalButtonGroup = Config::get('engineer-management.modalClasses.modalButtonGroup');
    $modalText = Config::get('engineer-management.modalClasses.modalText');
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnGrey');
@endphp

@props(['action', 'title' => 'Engineer','save'])

<div class="{{ $modalMain }}" wire:keydown.escape="{{ $action }}">
    <div class="{{ $modalBackdrop }}" wire:click="{{ $action }}"></div>
    <div class="{{ $modalPanel }}">
        <div class="{{ $modalHeaderPanel }}">
            <h3 class="{{ $modalHeader }}">{{ $title ?? 'Engineer' }}</h3>
            <button type="button" class="inline-flex items-center gap-2 cursor-pointer {{ $modalXButton }}" aria-label="Close" wire:click="{{ $action }}">×</button>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                {{ $modalContent }}
            </div>
        </div>
        <div class="{{ $modalButtonGroup }}">
            <button type="button" class="inline-flex items-center gap-2 cursor-pointer {{ $tdActionLink }}" wire:click="{{ $action }}">Close</button>
            <button type="button" class="inline-flex items-center gap-2 cursor-pointer {{ $tdActionLink }}" wire:click="{{ $save }}"
                wire:loading.attr="disabled">Save</button>
        </div>
    </div>
</div>
