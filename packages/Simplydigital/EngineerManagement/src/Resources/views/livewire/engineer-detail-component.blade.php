@php
    $ul = Config::get('engineer-management.listClass.ul');
    $li = Config::get('engineer-management.listClass.li');
    $liLeft = Config::get('engineer-management.listClass.liLeftItem');
    $liRightTop = Config::get('engineer-management.listClass.liRightTopItem');
    $liRightBottom = Config::get('engineer-management.listClass.liRightBottomItem');

    $containerClass = 'p-2 rounded-lg shadow-md m-4';
    $headerClass = 'text-xl font-semibold tracking-tight text-gray-800 dark:text-gray-300';
    $ul = 'bg-white shadow-lg rounded-xl divide-y divide-gray-200 m-4';
    $headerClass2 = 'text-lg font-medium text-gray-800 dark:text-gray-300 m-4 text-center';
    $ulSkill = str_replace('bg-white', 'bg-stone-400', $ul);
    $ulCertificate = str_replace('bg-white', 'bg-zinc-400', $ul);

@endphp

<div>
    <div class="{{ $containerClass }}">
        <h1 class="{{ $headerClass }} text-center">{{ $engineer->user->name }}</h1>
    </div>

    <div class="group m-4">
        <div class="cursor-pointer group-hover:hidden">
            <x-engineermanagement-ui-icon name="left" class="h-10 w-10 text-gray-300 dark:text-gray-300" />
        </div>
        <button class="hidden group-hover:block text-xl text-gray-500 cursor-pointer" wire:click="closeComponent">Back
            to List
        </button>
    </div>

    @if($openCertificateModal)
        <livewire:addupdateengineercertificatesmodal-component 
            :engineerCertificateId="$selectedEngineerCertificateId" 
            :engineerId="$engineerId" 
            :originExternalForUpdate="true" 
        />
    @endif

    @if($openSkillModal)
        <livewire:addupdateengineerskillsmodal-component 
            :engineerSkillId="$selectedEngineerSkillId" 
            :engineerId="$engineerId" 
            :originExternalForUpdate="true" 
        />
    @endif

    @if ($engSkills->isNotEmpty())
        <div class="flex flex-row gap-2 w-full m-4">
            <div class="w-1/2">
                <h2 class="{{ $headerClass2 }}">Skills</h2>
                @foreach ($engSkills as $skill)
                    <ul class="{{ $ulSkill }}">
                        <li class="{{ $li }}">
                            <div class="{{ $liLeft }}">
                                <button class="cursor-pointer" wire:click="openSkill({{ $skill->id }})">
                                    <x-engineermanagement-ui-icon name="skill" class="h-6 w-6 text-gray-900" />
                                </button>
                            </div>
                            <div class="flex gap-2 flex-col">
                                <div class="{{ $liRightTop }} flex gap-2 flex-col">
                                    <strong class="text-gray-900">{{ $skill->skill->skills }}</strong>
                                    <strong class="text-gray-900">Level: {{ $skill->getSkillLevelValue() }}</strong>
                                </div>
                                <div class="{{ $liRightBottom }}">
                                    <strong class="text-gray-900 text-xs">{{ $skill->number }}</strong>
                                </div>
                            </div>
                        </li>
                    </ul>
                @endforeach
            </div>

            <div class="w-1/2">
                <h2 class="{{ $headerClass2 }}">Certifications</h2>
                @foreach ($engCertifications as $cert)
                    @php
                        $expired = $cert->isExpired() ?? false;
                        $hasExpired = $expired ? '( EXPIREDs )' : null;
                    @endphp
                    <ul class="{{ $ulCertificate }}">
                        <li class="{{ $li }}">
                            <div class="{{ $liLeft }}">
                                <button class="cursor-pointer" wire:click="openCertificate({{ $cert->id }})">
                                    <x-engineermanagement-ui-icon name="certificate" class="h-6 w-6 text-gray-900"/>
                                </button>
                            </div>
                            <div class="flex gap-2 flex-col">
                                <div class="{{ $liRightTop }} flex gap-2 flex-col">
                                    <strong class="text-gray-900">{{ $cert->certificate->name }}
                                        {{ $hasExpired }}</strong>
                                    <strong class="text-gray-900">{{ $cert->certificate->getTypeValue() }}</strong>
                                </div>
                                <div class="{{ $liRightBottom }}">
                                    <strong class="text-gray-900 text-xs">{{ $cert->certificate->number }}</strong>
                                </div>
                            </div>
                        </li>
                    </ul>
                @endforeach
            </div>

        </div>
    @endif
</div>
