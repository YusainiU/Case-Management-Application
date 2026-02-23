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

    $inputLabel = "dark:text-gray-300 $inputLabel";
    $inputNormal =
        'appearance-none text-gray-900 bg-white block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500';
@endphp

<div>

    @if ($displayFilterOptions)
        <!-- Top Navigation Bar -->
        <div class="flex gap-4 p-4 rounded">
            <!-- Engineer -->
            <div class="flex-1">
                <label class="{{ $inputLabel }}">Select Engineer</label>
                <select wire:change="selectEngineer($event.target.value)" class="{{ $inputNormal }}">
                    <option value="">-- Select Engineer --</option>
                    @foreach ($engineers as $engineer)
                        <option value="{{ $engineer->id }}">{{ $engineer->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Skill -->
            <div class="flex-1">
                <label class="{{ $inputLabel }}">Select Skill</label>
                <select wire:model="skillId" wire:change="selectSkill($event.target.value)" class="{{ $inputNormal }}">
                    <option value="">-- Select Skill --</option>
                    @foreach ($skills as $skill)
                        <option value="{{ $skill->id }}">{{ $skill->skills }}</option>
                    @endforeach
                </select>
            </div>

            <!-- CertificationS -->
            <div class="flex-1">
                <label class="{{ $inputLabel }}">Select Certification</label>
                <select wire:model="certificationId" wire:change="selectCertification($event.target.value)" class="{{ $inputNormal }}">
                    <option value="latest">-- Select Certificate --</option>
                    @foreach ($certifications as $certification)
                        <option value="{{$certification->id }}">{{ $certification->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        @if($selectedEngineer)
            <livewire:engineerdetail-component :engineerId="$engineerId" />
        @endif
        @if($selectedSkill) 
            <livewire:engineerskill-component :skillId="$skillId" />
        @endif
        @if($selectedCertification)
            <livewire:engineercertificate-component :certificateId="$certificationId" :engineerId="$engineerId" />
        @endif   
    @endif

</div>

