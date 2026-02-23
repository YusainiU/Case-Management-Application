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

    {{-- Modal Add/Update Engineer --}}    
    @if ($modalMode)
        <x-engineermanagement::modal-form :action="$modalMode" :title="$modalTitle" :save="$modalSave">
            <x-slot name="modalContent">
                <div>
                    <label class="{{ $inputLabel }}">Name:</label>
                    <div>
                        @if($showAddModal)
                            <select class="{{ $inputNormal }} @error('user_id') {{ $inputError }} @enderror"
                                wire:model="user_id">
                                <option value="">-- Select User --</option>
                                @foreach ($users as $userid => $username)
                                            <option value="{{ $userid }}">{{ $username }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" class="{{ $inputNormal }}" value="{{ $user_id ?? '' }}" disabled />
                        @endif
                        @error('user_id')
                            <p class="{{ $errorMessage }}">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="{{ $inputLabel }}">Manager:</label>
                    <div>
                        <select class="{{ $inputNormal }} @error('manager') {{ $inputError }} @enderror"
                            wire:model="manager">
                            <option value="">-- Select Manager --</option>
                            @if($managers->isEmpty())
                                <option value="">No managers available</option>
                            @else
                                @foreach ($managers as $managerid => $managername)
                                            <option value="{{ $managerid }}">{{ $managername }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @error('manager')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $inputLabel }}">Position:</label>
                    <div>
                        <select class="{{ $inputNormal }} @error('position') {{ $inputError }} @enderror"
                            wire:model="position">
                            <option value="">-- Select Position --</option>
                            @foreach($listOfPositions as $positionid => $position)
                                <option value="{{ $positionid }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('position')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $inputLabel }}">Department:</label>
                    <div>
                        <select class="{{ $inputNormal }} @error('department') {{ $inputError }} @enderror"
                            wire:model="department">
                            <option value="">-- Select Department --</option>
                            @foreach($listOfDepartments as $departmentid => $department)
                                <option value="{{ $departmentid }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('department')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <div class="flex items-center gap-2 align-top">
                        <input type="checkbox" class="@error('active') {{ $checkbox }} @enderror"
                            wire:model="active" />
                        <label class="{{ $checkboxLabel }}">Active</label>
                    </div>
                    @error('active')
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
            </x-slot>
        </x-engineermanagement::modal-form>    
    @endif

</div>