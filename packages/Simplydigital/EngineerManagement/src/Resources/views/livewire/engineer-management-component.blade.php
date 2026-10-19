@php
    $tdClass = Config::get('engineer-management.tableClasses.td');
    $thClass = Config::get('engineer-management.tableClasses.th');
    $trThClass = Config::get('engineer-management.tableClasses.colTr');
    $divTopClass = Config::get('engineer-management.tableClasses.divTop');
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnBlue');
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

<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    {{-- Modal Add Engineer --}}
    @if ($modalMode)
        <livewire:addupdateengineermodal-component 
            :mode="$modalMode" 
            :modalTitle="$modalTitle" 
            :engineerManagerId="$engineerManagerId"
            @close-modal="closeModal" 
        />
    @endif

    {{-- Modal List ENgineer Skills --}}
    @if($modalSkill)
        <livewire:addupdateengineerskillsmodal-component 
            :engineerId="$engineerId" 
            @close-modal="closeModal"
        />
    @endif

    {{-- Modal List of Engineer Certificates --}}
    @if($modalCertificate)
        <livewire:addupdateengineercertificatesmodal-component 
            :engineerId="$engineerId" 
            @close-modal="closeModal"
        />
    @endif

    <x-engineermanagement::section-title>
        <x-slot name="title">
            <div class="flex items-center gap-3 text-slate-500 rounded-2xl border border-slate-300 bg-slate-200 p-4 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200 text-violet-600">
                    <x-engineermanagement-ui-icon name="engineer" class="h-10 w-10" />
                </div>
                <span class="text-2xl font-bold tracking-tight sm:text-3xl text-slade-50 dark:text-slade-50">
                    Engineer Management
                </span>
            </div>            
        </x-slot>
        <x-slot name="description">
            Manage your engineers effectively.
        </x-slot>
    </x-engineermanagement::section-title>

    <div class="mt-3 mb-3">
        <button type="button" class="{{ $tdActionLink }}" wire:click="toggleAddModal">
            <x-engineermanagement-ui-icon name="add" class="h-6 w-6" />
            Add Engineer
        </button>
    </div>

    <div class="mt-3 mb-3 flex justify-between gap-x-6 py-5">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>

    @if ($showList)
        {{ $engineers->links() }}
    @endif

    <x-engineermanagement::table>
        <x-slot name="tableColumns">
            <x-engineermanagement::table-th :items="[
                'ID',
                'Name',
                'Manager',
                'Position',
                'Active',
                'Actions',
            ]" />
        </x-slot>
        <x-slot name="tableRows">
            @if ($showList && !$engineers->isEmpty())
                @foreach ($engineers as $engineer)
                    <tr class="divide-y divide-slate-100 bg-white">
                        <x-engineermanagement::table-td>{{ $engineer->id }}</x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>{{ $engineer->user->name ?? '-' }}</x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>{{ $engineer->engmanager->name ?? '-' }}</x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>{{ $engineer->position ?? '-' }}</x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>{{ $engineer->active ? 'Yes' : 'No' }}</x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>
                            <x-engineermanagement::button-update label="Update" action="toggleEditModal({{ $engineer->id }})" />
                            <x-engineermanagement::button-view-skill label="View Skills" action="toggleSkillModal({{ $engineer->id }})" />
                            <x-engineermanagement::button-certificate label="View Certificates" action="toggleCertificateModal({{ $engineer->id }})" />
                            <x-engineermanagement::button-delete label="Delete" recordId="{{ $engineer->id }}" />                        
                        </x-engineermanagement::table-td>
                    </tr>
                @endforeach
            @else
                    <x-engineermanagement::table-tr-nodata colspan='6' />
            @endif
        </x-slot>
    </x-engineermanagement::table>

    @if ($showList)
        {{ $engineers->links() }}
    @endif

</div>
