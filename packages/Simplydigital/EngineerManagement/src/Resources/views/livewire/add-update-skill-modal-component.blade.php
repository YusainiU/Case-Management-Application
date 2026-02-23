@php
    $tdClass = Config::get('engineer-management.tableClasses.td');
    $thClass = Config::get('engineer-management.tableClasses.th');
    $trThClass = Config::get('engineer-management.tableClasses.colTr');
    $divTopClass = Config::get('engineer-management.tableClasses.divTop');
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnGrey');
    $tdActionLinkDelete = Config::get('engineer-management.buttonClasses.btnRed');
    $table = Config::get('engineer-management.tableClasses.table');

    $inputNormal = Config::get('engineer-management.formClasses.input-normal');
    $inputError = Config::get('engineer-management.formClasses.input-error');
    $inputLabel = Config::get('engineer-management.formClasses.label');
    $checkboxLabel = Config::get('engineer-management.formClasses.checkboxLabel');
    $checkbox = Config::get('engineer-management.formClasses.checkbox');
    $errorMessage = Config::get('engineer-management.formClasses.error-message');

@endphp

<div class="{{ $divTopClass }}">

    {{-- Modal Add/Update Skill --}}
    <x-engineermanagement::modal-form :action="$modalClose" :title="$modalTitle" :save="$modalAction">
        <x-slot name="modalContent">
            <div>
                <label class="{{ $inputLabel }}">Skill Name:</label>
                <div>
                    <input type="text" class="{{ $inputNormal }} @error('name') {{ $inputError }} @enderror"
                        wire:model="name" />
                    @error('name')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label class="{{ $inputLabel }}">Description:</label>
                <div>
                    <textarea class="{{ $inputNormal }} @error('description') {{ $inputError }} @enderror"
                        wire:model="description"></textarea>
                    @error('description')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label class="{{ $checkboxLabel }}">
                    <input type="checkbox" class="{{ $checkbox }}" wire:model="active" />
                    Active
                </label>
            </div>  
        </x-slot>
    </x-engineermanagement::modal-form>


</div>
