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

    <x-engineermanagement::modal-form :action="$modalClose" :title="$modalTitle" :save="$modalAction">
        <x-slot name="modalContent">
            <div>
                <label class="{{ $inputLabel }}">Certificate Name:</label>
                <div>
                    <input type="text" class="{{ $inputNormal }} @error('name') {{ $inputError }} @enderror"
                        wire:model="name" />
                    @error('name')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label class="{{ $inputLabel }}">Type:</label>
                <select class="{{ $inputNormal }} @error($type) {{ $inputError }} @enderror" wire:model="type">
                    <option value="">-- Select Type --</option>
                    @if (sizeof($certificateTypes) > 0)
                        @foreach ($certificateTypes as $key => $ctype)
                            <option value="{{ $key }}">{{ $ctype }}</option>
                        @endforeach
                    @endif
                </select>
                @error($type)
                    <p class="{{ $errorMessage }}">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="{{ $inputLabel }}">Issued By:</label>
                <select class="{{ $inputNormal }} @error($type) {{ $inputError }} @enderror" wire:model="issued_by">
                    <option value="">-- Select Issuer --</option>
                    @if (sizeof($certificateIssuers) > 0)
                        @foreach ($certificateIssuers as $key => $issuer)
                            <option value="{{ $issuer }}">{{ $issuer }}</option>
                        @endforeach
                    @endif
                </select>
                @error($issued_by)
                    <p class="{{ $errorMessage }}">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="{{ $checkboxLabel }} dark:text-gray-800">
                    <div class="flex items-center gap-2 align-top">
                        <input type="checkbox" class="{{ $checkbox }}" wire:model="active" />
                        <span>Active</span>
                    </div>
                </label>
            </div>                          
        </x-slot>
    </x-engineermanagement::modal-form>

</div>
