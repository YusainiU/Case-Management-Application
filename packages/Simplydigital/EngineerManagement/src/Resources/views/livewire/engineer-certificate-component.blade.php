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
    $ulCert = str_replace('bg-white', 'bg-stone-400', $ul);

@endphp

<div>
    <div class="{{ $containerClass }}">
        <h1 class="{{ $headerClass }} text-center">{{ $certificate->name }}</h1>
    </div>
    <div class="group m-4">
        <div class="cursor-pointer group-hover:hidden">
            <x-engineermanagement-ui-icon name="left" class="h-10 w-10 text-gray-300 dark:text-gray-300" />
        </div>
        <button class="hidden group-hover:block text-xl text-gray-500 cursor-pointer" wire:click="closeComponent">Back
            to List
        </button>
    </div>
    @if ($displayEngineerCertificateDetails)
        <livewire:addupdateengineercertificatesmodal-component :originExternalForUpdate="true" :engineerCertificateId="$engineerCertificateId" :engineerId="$engineerId" />
    @endif

    @if ($engineerWithCertificates->isNotEmpty())
        <div class="flex flex-col gap-2 w-full">
            <div class="w-full">
                <h2 class="{{ $headerClass2 }}">Engineers with Selected Certification</h2>
            </div>
        </div>
        @foreach($engineerWithCertificates as $eng)
            <ul class="{{ $ulCert }}">
                <li class="{{ $li }}">
                    <div class="{{ $liLeft }}">
                        <button class="cursor-pointer" wire:click="openEngineerCertificate({{ $eng->id }}, {{ $eng->engineer_id }})">
                            <x-engineermanagement-ui-icon name="certificate" class="h-6 w-6 text-gray-900" />
                        </button>
                    </div>
                    <div class="flex gap-2 flex-col ml-4">
                        <div class="{{ $liRightTop }} flex gap-2 flex-col">
                            <strong class="text-gray-900">{{ $eng->engineer->user->name }}</strong>
                            <span class="text-gray-900">Issued Date: {{ $eng->formatIssuedDate() }}</span>
                            <span class="text-gray-900">
                                Expiry Date: {{ $eng->formatExpiryDate() }}
                                @if($eng->isExpired())
                                    <span class="text-red-900 font-bold">(Expired)</span>
                                @endif   
                            </span>
                            <span class="text-gray-900">
                                Issued By: {{ $eng->certificate->issued_by }}
                            </span>
                        </div>
                        <div class="{{ $liRightBottom }}">
                            <strong class="text-gray-900 text-xs">Certificate Number: {{ $eng->certification_number }}</strong>
                        </div>
                    </div>                            
                </li>
            </ul>
        @endforeach
    @endif
</div>
