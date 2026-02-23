@php
    $tdClass = Config::get('engineer-management.tableClasses.td');
    $thClass = Config::get('engineer-management.tableClasses.th');
    $trThClass = Config::get('engineer-management.tableClasses.colTr');
    $divTopClass = Config::get('engineer-management.tableClasses.divTop');
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnGrey');
    $tdActionLinkDelete = Config::get('engineer-management.buttonClasses.btnRed');
    $table = Config::get('engineer-management.tableClasses.table');

    $tdActionLink = "inline-flex items-center gap-2 cursor-pointer {{ $tdActionLink }}";
    $tdActionLinkDelete = "inline-flex items-center gap-2 cursor-pointer {{ $tdActionLinkDelete }}";

    $inputNormal = Config::get('engineer-management.formClasses.input-normal');
    $inputError = Config::get('engineer-management.formClasses.input-error');
    $inputLabel = Config::get('engineer-management.formClasses.label');
    $checkboxLabel = Config::get('engineer-management.formClasses.checkboxLabel');
    $checkbox = Config::get('engineer-management.formClasses.checkbox');
    $errorMessage = Config::get('engineer-management.formClasses.error-message');

@endphp

<div class="{{ $divTopClass }}">

    {{-- Modal Add Engineer --}}
    @if ($modalMode)
        <livewire:addupdateengineermodal-component :mode="$modalMode" :modalTitle="$modalTitle" :engineerManagerId="$engineerManagerId" />
    @endif

    {{-- Modal List ENgineer Skills --}}
    @if($modalSkill)
        <livewire:addupdateengineerskillsmodal-component :engineerId="$engineerId" />
    @endif

    {{-- Modal List of Engineer Certificates --}}
    @if($modalCertificate)
        <livewire:addupdateengineercertificatesmodal-component :engineerId="$engineerId" />
    @endif

    <x-engineermanagement::section-title>
        <x-slot name="title">
            <div>
            <x-engineermanagement-ui-icon name="engineer" class="h-10 w-10" />
            Engineer Management
            </div>
        </x-slot>
        <x-slot name="description">
            Manage your engineers effectively.
        </x-slot>
    </x-engineermanagement::section-title>

    <div class="{{ $divSeparator }}">
        &nbsp;
    </div>

    <div class="mt-3 mb-3 px-5">
        <button type="button" class="{{ $tdActionLink }}" wire:click="toggleAddModal">
            <x-engineermanagement-ui-icon name="add" class="h-6 w-6" />
            Add Engineer
        </button>
    </div>

    <div class="mt-3 mb-3 flex justify-between gap-x-6 py-5 px-5">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>

    @if ($showList)
        {{ $engineers->links() }}
    @endif

    <x-engineermanagement::table>
        <x-slot name="tableColumns">
            <tr class="{{ $trThClass }}">
                <th class="{{ $thClass }}">ID</th>
                <th class="{{ $thClass }}">Name</th>
                <th class="{{ $thClass }}">Manager</th>
                <th class="{{ $thClass }}">Position</th>
                <th class="{{ $thClass }}">Active</th>
                <th class="{{ $thClass }}">Actions</th>
            </tr>
        </x-slot>
        <x-slot name="tableRows">
            @if ($showList && !$engineers->isEmpty())
                @foreach ($engineers as $engineer)
                    <tr class="hover:bg-gray-50">
                        <td class="{{ $tdClass }}">{{ $engineer->id }}</td>
                        <td class="{{ $tdClass }}">{{ $engineer->user->name ?? '-' }}</td>
                        <td class="{{ $tdClass }}">{{ $engineer->engmanager->name ?? '-' }}</td>
                        <td class="{{ $tdClass }}">{{ $engineer->position ?? '-' }}</td>
                        <td class="{{ $tdClass }}">{{ $engineer->active ? 'Yes' : 'No' }}</td>
                        <td class="{{ $tdClass }}">
                            <button 
                                type="button" 
                                class="{{ $tdActionLink }}"
                                wire:click="toggleEditModal({{ $engineer->id }})"
                            >
                                <x-engineermanagement-ui-icon name="edit" class="h-6 w-6" />
                                Update
                            </button>
                            <button 
                                type="button" 
                                class="{{ $tdActionLink }}"
                                wire:click="toggleSkillModal({{ $engineer->id }})"
                            >
                                <x-engineermanagement-ui-icon name="skill" class="h-6 w-6" />
                                View Skills
                            </button>
                            <button 
                                type="button" 
                                class="{{ $tdActionLink }}"
                                wire:click="toggleCertificateModal({{ $engineer->id }})"
                            >
                                <x-engineermanagement-ui-icon name="certificate" class="h-6 w-6" />
                                View Certificates
                            </button>                            
                            <button 
                                type="button" 
                                class="{{ $tdActionLinkDelete }}"
                                wire:click="delete({{ $engineer->id }})"
                                wire:confirm="Continue to remove engineer?"
                            >
                                <x-engineermanagement-ui-icon name="minuscircle" class="h-6 w-6" />
                                Delete
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

    @if ($showList)
        {{ $engineers->links() }}
    @endif

</div>
