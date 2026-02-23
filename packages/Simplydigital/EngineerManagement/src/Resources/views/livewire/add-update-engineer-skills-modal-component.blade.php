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

    $ul = Config::get('engineer-management.listClass.ul');
    $li = Config::get('engineer-management.listClass.li');
    $liLeft = Config::get('engineer-management.listClass.liLeftItem');
    $liRightTop = Config::get('engineer-management.listClass.liRightTopItem');
    $liRightBottom = Config::get('engineer-management.listClass.liRightBottomItem');

@endphp

<div class="{{ $divTopClass }}">

    <x-engineermanagement::modal-form :action="$modalMode" :title="$modalTitle" :save="$modalSave">
        <x-slot name="modalContent">
            <h4 class="text-gray-200 dark:text-gray-900">
                {{ $engineer->user->name }}
            </h4>
            <div class="group">
                @if ($displayList)
                    <span class="cursor-pointer group-hover:hidden">
                        <x-engineermanagement-ui-icon name="addcircle" class="h-6 w-6" />
                    </span>
                    <button class="hidden group-hover:block text-sm text-gray-500 cursor-pointer"
                        wire:click="createNewSkill">Add Skill
                    </button>
                @else
                    <span class="cursor-pointer group-hover:hidden">
                        <x-engineermanagement-ui-icon name="left" class="h-6 w-6 text-gray-900" />
                    </span>
                    <button class="hidden group-hover:block text-sm text-gray-500 cursor-pointer"
                        wire:click="backToList">Back to List
                    </button>
                @endif
            </div>
            @if ($displayList)
                <ul class="{{ $ul }}">
                    @foreach ($engineerSkills as $enSkill)
                        <li class="{{ $li }}">
                            <div class="group">
                                <span class="{{ $liLeft }} group-hover:hidden">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="hidden group-hover:block">
                                    <button class="cursor-pointer" wire:click="updateSkill({{ $enSkill->id }})">
                                        <x-engineermanagement-ui-icon name="edit" class="h-8 w-8 text-gray-900" />
                                    </button>
                                </span>
                            </div>
                            <div>
                                <p class="{{ $liRightTop }} text-gray-900">
                                    <span class="block">
                                        {{ $enSkill->skill->skills }} - {{ $enSkill->skill_level }}
                                    </span>
                                </p>
                                <p class="{{ $liRightBottom }} text-gray-900">{{ $enSkill->description }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div>
                    <label class="{{ $inputLabel }}">Skill:</label>
                    <select class="{{ $inputNormal }} @error($skill_id) {{ $inputError }} @enderror"
                        wire:model="skill_id">
                        <option value="">-- Select Skill --</option>
                        @if (sizeof($skills))
                            @foreach ($skills as $key => $skill)
                                <option value="{{ $key }}">{{ $skill }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error($skill_id)
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $inputLabel }}">Level:</label>
                    <select class="{{ $inputNormal }} @error($skill_level) {{ $inputError }} @enderror"
                        wire:model="skill_level">
                        <option value="">-- Select Skill Level --</option>
                        @if (sizeof($skill_levels))
                            @foreach ($skill_levels as $key => $level)
                                <option value="{{ $key }}">{{ $level }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error($skill_level)
                        <p class="{{ $errorMessage }}">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $inputLabel }}">Description:</label>
                    <div>
                        <textarea class="{{ $inputNormal }} @error('description') {{ $inputError }} @enderror" wire:model="description"></textarea>
                        @error('description')
                            <p class="{{ $errorMessage }}">{{ $message }}</p>
                        @enderror
                    </div>
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
            @endif
        </x-slot>
    </x-engineermanagement::modal-form>

</div>
