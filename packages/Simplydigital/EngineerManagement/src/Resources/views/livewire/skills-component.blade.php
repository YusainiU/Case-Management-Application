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
        <livewire:addupdateskillmodal-component :mode="$modalMode" :modalTitle="$modalTitle" :skillId="$skillId" />
    @endif

    <x-engineermanagement::section-title>
        <x-slot name="title">
            <x-engineermanagement-ui-icon name="skill" class="h-10 w-10" />
            Skill Management
        </x-slot>
        <x-slot name="description">
            List of Skills
        </x-slot>
    </x-engineermanagement::section-title>

    <div class="{{ $divSeparator }}">
        &nbsp;
    </div>

    <div class="mt-3 mb-3 px-5">
        <button type="button" class="{{ $tdActionLink }}" wire:click="toggleAddModal">
            <x-engineermanagement-ui-icon name="add" class="h-6 w-6" />
            Add Skill
        </button>
    </div>

    <div class="mt-3 mb-3 flex justify-between gap-x-6 py-5 px-5">
        <x-input name="filter" class="" placeholder="Search records" wire:model.live='filter' />
    </div>

    @if ($showList)
        {{ $skills->links() }}
    @endif

    <x-engineermanagement::table>
        <x-slot name="tableColumns">
            <tr class="{{ $trThClass }}">
                <th class="{{ $thClass }}">Name</th>
                <th class="{{ $thClass }}">Description</th>
                <th class="{{ $thClass }}">Active</th>
                <th class="{{ $thClass }}">Actions</th>
            </tr>
        </x-slot>

        <x-slot name="tableRows">
            @if ($showList)
                @foreach ($skills as $skill)
                    <tr>
                        <td class="{{ $tdClass }}">{{ $skill->skills }}</td>
                        <td class="{{ $tdClass }}">{{ $skill->description }}</td>
                        <td class="{{ $tdClass }}">
                            @if ($skill->active)
                                Yes
                            @else
                                No
                            @endif
                        <td class="{{ $tdClass }}">
                            <button type="button" class="{{ $tdActionLink }}"
                                wire:click="toggleUpdateModal({{ $skill->id }})"
                            >
                                <x-engineermanagement-ui-icon name="edit" class="h-6 w-6" />
                                Edit
                            </button>
                            <button type="button" class="{{ $tdActionLinkDelete }}"
                                wire:click="delete({{ $skill->id }})"
                                wire:confirm="Are you sure you want to delete this skill?"
                            >
                                <x-engineermanagement-ui-icon name="crosscircle" class="w-6 h-6" />
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </x-slot>

    </x-engineermanagement::table>

    @if($showList)
        {{ $skills->links() }}
    @endif

</div
