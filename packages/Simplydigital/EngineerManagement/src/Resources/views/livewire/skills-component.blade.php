@php
    $tdClass = Config::get('engineer-management.tableClasses.td');
    $thClass = Config::get('engineer-management.tableClasses.th');
    $trThClass = Config::get('engineer-management.tableClasses.colTr');
    $divTopClass = "mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8";
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnBlue');
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
        <livewire:addupdateskillmodal-component 
            :mode="$modalMode" 
            :modalTitle="$modalTitle" 
            :skillId="$skillId" 
            @close-modal="closeModal"
        />
    @endif

    <x-engineermanagement::section-title>
        <x-slot name="title">
            <div class="flex items-center gap-3 text-slate-500 rounded-2xl border border-slate-300 bg-slate-200 p-4 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200 text-violet-600">
                    <x-engineermanagement-ui-icon name="skill" class="h-10 w-10" />
                </div>
                <span class="text-2xl font-bold tracking-tight sm:text-3xl text-slade-50 dark:text-slade-50">
                    Skill Management
                </span>
            </div>            
        </x-slot>
        <x-slot name="description">
            List of Skills.
        </x-slot>
    </x-engineermanagement::section-title>    

    <div class="mt-3 mb-3">
        <button type="button" class="{{ $tdActionLink }}" wire:click="toggleAddModal">
            <x-engineermanagement-ui-icon name="add" class="h-6 w-6" />
            Add Skill
        </button>
    </div>

    <div class="mt-3 mb-3 flex justify-between gap-x-6 py-5">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>

    @if ($showList)
        {{ $skills->links() }}
    @endif

    <x-engineermanagement::table>
        <x-slot name="tableColumns">
            <x-engineermanagement::table-th :items="[
                'Name',
                'Description',
                'Active',
                'Actions'
            ]" />
        </x-slot>

        <x-slot name="tableRows">
            @if ($showList)
                @foreach ($skills as $skill)
                    <tr>
                        <x-engineermanagement::table-td>
                            {{ $skill->skills }}
                        </x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>
                            {{ $skill->description }}
                        </x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>
                            @if ($skill->active)
                                Yes
                            @else
                                No
                            @endif
                        </x-engineermanagement::table-td>
                        <x-engineermanagement::table-td>
                            <x-engineermanagement::button-update label="Edit" action="toggleUpdateModal({{ $skill->id }})" />
                            <x-engineermanagement::button-delete label="Delete" recordId="{{ $skill->id }}" />
                        </x-engineermanagement::table-td>
                    </tr>
                @endforeach
            @else
                <x-engineermanagement::table-tr-nodata colspan='4' />
            @endif
        </x-slot>

    </x-engineermanagement::table>

    @if($showList)
        {{ $skills->links() }}
    @endif

</div
