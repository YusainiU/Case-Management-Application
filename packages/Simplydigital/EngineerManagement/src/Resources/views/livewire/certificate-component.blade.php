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

    $tdActionLink = "inline-flex items-center gap-2 cursor-pointer {{ $tdActionLink }}";
    $tdActionLinkDelete = "inline-flex items-center gap-2 cursor-pointer {{ $tdActionLinkDelete }}";

@endphp

<div class="{{ $divTopClass }}">
    @if ($modalMode)
        <livewire:addupdatecertificatemodal-component :mode="$modalMode" :modalTitle="$modalTitle" :certificateId="$certificateId" />
    @endif

    <x-engineermanagement::section-title>
        <x-slot name="title">
            <x-engineermanagement-ui-icon name="certificate" class="h-10 w-10" />
            Certificate Management
        </x-slot>
        <x-slot name="description">
            List of Certificates
        </x-slot>
    </x-engineermanagement::section-title>

    <div class="{{ $divSeparator }}">
        &nbsp;
    </div>

    <div class="mt-3 mb-3 px-5">
        <button type="button" class="{{ $tdActionLink }}" wire:click="toggleAddModal">
            <x-engineermanagement-ui-icon name="add" class="h-6 w-6" />
            Add Certificate
        </button>
    </div>

    <div class="mt-3 mb-3 flex justify-between gap-x-6 py-5 px-5">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>

    @if ($showList)
        {{ $certificates->links() }}
    @endif

    <x-engineermanagement::table>
        <x-slot name="tableColumns">
            <tr class="{{ $trThClass }}">
                <th class="{{ $thClass }}">Name</th>
                <th class="{{ $thClass }}">Issued By</th>
                <th class="{{ $thClass }}">Active</th>
                <th class="{{ $thClass }}">Actions</th>
            </tr>
        </x-slot>
        <x-slot name="tableRows">
            @if ($showList)
                @foreach ($certificates as $certificate)
                    <tr class="">
                        <td class="{{ $tdClass }}">{{ $certificate->name }}</td>
                        <td class="{{ $tdClass }}">{{ $certificate->issued_by }}</td>
                        <td class="{{ $tdClass }}">
                            @if ($certificate->active)
                                Yes
                            @else
                                No
                            @endif
                        </td>
                        <td class="{{ $tdClass }}">
                            <button type="button" class="{{ $tdActionLink }}" wire:click="toggleUpdateModal({{ $certificate->id }})">
                                <x-engineermanagement-ui-icon name="edit" class="h-5 w-5" />
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="{{ $tdClass }}" colspan="6">No records found.</td>
                </tr>
            @endif
        </x-slot>
    </x-engineermanagement::table>

</div>