
@php
    $tdClass = Config::get('engineer-management.tableClasses.td');
    $thClass = Config::get('engineer-management.tableClasses.th');
    $trThClass = Config::get('engineer-management.tableClasses.colTr');
    $divTopClass = Config::get('engineer-management.tableClasses.divTop');
    $divSeparator = Config::get('engineer-management.tableClasses.divSeparator');
    $tdActionLink = Config::get('engineer-management.buttonClasses.btnGrey');
    $tdActionLinkDelete = Config::get('engineer-management.buttonClasses.btnRed');
    $table = Config::get('engineer-management.tableClasses.table');

    $inputNormal = 'block w-full rounded-xl border border-gray-200 bg-white
                    px-4 py-3 text-sm text-gray-800 shadow-sm outline-none
                    transition placeholder:text-gray-400
                    hover:border-gray-300
                    focus:border-indigo-500 focus:ring-4
                    focus:ring-indigo-500/10';

    $inputError = Config::get('engineer-management.formClasses.input-error');
    $inputLabel = 'mb-2 block text-sm font-medium text-gray-700';
    $checkboxLabel = Config::get('engineer-management.formClasses.checkboxLabel');
    $checkbox = Config::get('engineer-management.formClasses.checkbox');
    $errorMessage = Config::get('engineer-management.formClasses.error-message');
@endphp

<div class="w-full min-w-0">

    {{-- Summary wrapper --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200
                bg-white shadow-[0_6px_0_0_rgba(15,23,42,0.025),0_10px_24px_-12px_rgba(15,23,42,0.12)]">

        @if ($displayFilterOptions)

            {{-- Header --}}
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6 sm:py-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center
                                justify-center rounded-xl bg-indigo-50
                                text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="10" cy="7" r="4"/>
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold tracking-tight
                                   text-gray-900">
                            Engineer Summary
                        </h2>
                        <p class="mt-1 text-sm leading-5 text-gray-500">
                            Explore engineers, technical skills and
                            professional certifications.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Filter controls --}}
            <div class="bg-gray-50/60 p-5 sm:p-6">
                <div class="mb-5">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Filter records
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Select an option below to view the relevant details.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2
                            xl:grid-cols-3">

                    {{-- Engineer --}}
                    <div class="min-w-0">
                        <label for="summary-engineer"
                               class="{{ $inputLabel }}">
                            <span class="mb-1.5 flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-500"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path stroke-linecap="round"
                                          d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                                </svg>
                                Engineer
                            </span>
                        </label>

                        <select
                            id="summary-engineer"
                            wire:change="selectEngineer($event.target.value)"
                            class="{{ $inputNormal }}"
                        >
                            <option value="">All engineers / Select engineer</option>
                            @foreach ($engineers as $engineer)
                                <option value="{{ $engineer->id }}">
                                    {{ $engineer->user->name }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-gray-400">
                            View an individual engineer's profile.
                        </p>
                    </div>

                    {{-- Skill --}}
                    <div class="min-w-0">
                        <label for="summary-skill"
                               class="{{ $inputLabel }}">
                            <span class="mb-1.5 flex items-center gap-2">
                                <svg class="h-4 w-4 text-violet-500"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                </svg>
                                Technical skill
                            </span>
                        </label>

                        <select
                            id="summary-skill"
                            wire:model="skillId"
                            wire:change="selectSkill($event.target.value)"
                            class="{{ $inputNormal }}"
                        >
                            <option value="">All skills / Select skill</option>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}">
                                    {{ $skill->skills }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-gray-400">
                            Explore engineers by technical expertise.
                        </p>
                    </div>

                    {{-- Certification --}}
                    <div class="min-w-0 md:col-span-2 xl:col-span-1">
                        <label for="summary-certification"
                               class="{{ $inputLabel }}">
                            <span class="mb-1.5 flex items-center gap-2">
                                <svg class="h-4 w-4 text-sky-500"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linejoin="round"
                                          d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                                    <path stroke-linejoin="round"
                                          d="M14 3v6h5M9 14h6M9 17h6"/>
                                </svg>
                                Certification
                            </span>
                        </label>

                        <select
                            id="summary-certification"
                            wire:model="certificationId"
                            wire:change="selectCertification($event.target.value)"
                            class="{{ $inputNormal }}"
                        >
                            <option value="latest">Latest / Select certification</option>
                            @foreach ($certifications as $certification)
                                <option value="{{ $certification->id }}">
                                    {{ $certification->name }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-gray-400">
                            Review certification records and status.
                        </p>
                    </div>

                </div>
            </div>

            {{-- Footer hint --}}
            <div class="flex items-center gap-2 border-t border-gray-100
                        px-5 py-3.5 sm:px-6">
                <svg class="h-4 w-4 shrink-0 text-gray-400"
                     viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round"
                          d="M12 11v5M12 8h.01"/>
                </svg>
                <p class="text-xs text-gray-500">
                    Choose a filter to load the corresponding summary.
                </p>
            </div>

        @else

            {{-- Selected detail components --}}
            <div class="min-w-0">

                @if ($selectedEngineer)
                    <livewire:engineerdetail-component
                        :engineerId="$engineerId"
                        @close-filter="closeFilter"
                        @close-modal="closeModal"
                    />
                @endif

                @if ($selectedSkill)
                    <livewire:engineerskill-component
                        :skillId="$skillId"
                        @close-filter="closeFilter"
                        @close-modal="closeModal"
                    />
                @endif

                @if ($selectedCertification)
                    <livewire:engineercertificate-component
                        :certificateId="$certificationId"
                        :engineerId="$engineerId"
                        @close-filter="closeFilter"
                        @close-modal="closeModal"
                    />
                @endif

            </div>

        @endif

    </div>
</div>
