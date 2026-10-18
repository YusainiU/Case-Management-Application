
@php
    $ul = Config::get('engineer-management.listClass.ul');
    $li = Config::get('engineer-management.listClass.li');
    $liLeft = Config::get('engineer-management.listClass.liLeftItem');
    $liRightTop = Config::get('engineer-management.listClass.liRightTopItem');
    $liRightBottom = Config::get('engineer-management.listClass.liRightBottomItem');

    $containerClass = 'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_6px_0_0_rgba(15,23,42,0.025),0_10px_24px_-12px_rgba(15,23,42,0.12)]';
    $headerClass = 'text-2xl font-bold tracking-tight text-gray-900';
    $headerClass2 = 'text-lg font-semibold tracking-tight text-gray-900';
    $ul = 'divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white';
    $ulSkill = $ul;
@endphp

<div class="w-full min-w-0">

    {{-- Main wrapper --}}
    <div class="{{ $containerClass }}">

        {{-- Skill header --}}
        <div class="border-b border-gray-100 bg-white px-5 py-6
                    sm:px-7 sm:py-7">

            <div class="flex flex-col gap-5 sm:flex-row
                        sm:items-center sm:justify-between">

                <div class="flex min-w-0 items-center gap-4">

                    <div class="flex h-14 w-14 shrink-0 items-center
                                justify-center rounded-2xl bg-violet-50
                                text-violet-600 ring-1 ring-violet-100">
                        <x-engineermanagement-ui-icon
                            name="skill"
                            class="h-7 w-7"
                        />
                    </div>

                    <div class="min-w-0">
                        <p class="mb-1 text-xs font-semibold uppercase
                                  tracking-[0.15em] text-violet-600">
                            Technical Skill
                        </p>

                        <h1 class="{{ $headerClass }} break-words">
                            {{ $selectedSkill->skills }}
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Engineers with this skill and their proficiency levels.
                        </p>
                    </div>
                </div>

                {{-- Back button --}}
                <button
                    type="button"
                    wire:click="closeComponent"
                    class="inline-flex w-fit shrink-0 items-center
                           justify-center gap-2 rounded-xl border
                           border-gray-200 bg-white px-4 py-2.5
                           text-sm font-medium text-gray-700 shadow-sm
                           transition hover:border-gray-300
                           hover:bg-gray-50
                           focus:outline-none focus:ring-4
                           focus:ring-violet-500/10"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M15 18l-6-6 6-6M9 12h12"/>
                    </svg>
                    Back to list
                </button>

            </div>
        </div>

        {{-- Main content --}}
        <div class="space-y-6 bg-gray-50/60 p-4 sm:p-6 lg:p-7">

            {{-- Summary card --}}
            <div class="flex items-center gap-4 rounded-xl
                        border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center
                            justify-center rounded-xl bg-violet-50
                            text-violet-600">
                    <x-engineermanagement-ui-icon
                        name="user"
                        class="h-5 w-5"
                    />
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">
                        Engineers with this skill
                    </p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $engineersWithSkill->count() }}
                        {{ $engineersWithSkill->count() === 1 ? 'engineer assigned' : 'engineers assigned' }}
                    </p>
                </div>

                <span class="rounded-lg bg-violet-50 px-3 py-1.5
                             text-xs font-semibold text-violet-700">
                    Skill overview
                </span>
            </div>

            {{-- Engineer list --}}
            <section class="overflow-hidden rounded-2xl border
                            border-gray-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b
                            border-gray-100 px-5 py-4 sm:px-6">

                    <div class="flex h-10 w-10 shrink-0 items-center
                                justify-center rounded-xl bg-indigo-50
                                text-indigo-600">
                        <x-engineermanagement-ui-icon
                            name="user"
                            class="h-5 w-5"
                        />
                    </div>

                    <div class="min-w-0">
                        <h2 class="{{ $headerClass2 }}">
                            Engineers with Selected Skill
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Review each engineer's recorded skill level.
                        </p>
                    </div>

                    <span class="ml-auto rounded-lg bg-gray-100 px-2.5
                                 py-1 text-xs font-semibold text-gray-600">
                        {{ $engineersWithSkill->count() }}
                    </span>
                </div>

                <div class="space-y-3 p-4 sm:p-5">

                    @forelse ($engineersWithSkill as $eng)

                        <article
                            wire:key="engineer-skill-assignment-{{ $eng->id }}"
                            class="rounded-xl border border-gray-200 bg-white
                                   p-4 transition hover:border-violet-200
                                   hover:bg-violet-50/30"
                        >
                            <div class="flex items-start gap-3 sm:gap-4">

                                {{-- Engineer icon / action --}}
                                <button
                                    type="button"
                                    wire:click="openEngineerSkill({{ $eng->id }}, {{ $eng->engineer_id }})"
                                    aria-label="View skill details for {{ $eng->engineer->user->name }}"
                                    class="flex h-11 w-11 shrink-0 items-center
                                           justify-center rounded-xl bg-indigo-50
                                           text-indigo-600 transition
                                           hover:bg-indigo-100
                                           focus:outline-none focus:ring-4
                                           focus:ring-indigo-500/10"
                                >
                                    <x-engineermanagement-ui-icon
                                        name="user"
                                        class="h-5 w-5"
                                    />
                                </button>

                                {{-- Engineer details --}}
                                <div class="min-w-0 flex-1">

                                    <h3 class="break-words text-sm font-semibold
                                               text-gray-900">
                                        {{ $eng->engineer->user->name }}
                                    </h3>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Skill proficiency
                                    </p>

                                    <div class="mt-3 flex flex-wrap
                                                items-center gap-2">

                                        <span class="inline-flex items-center
                                                     gap-1.5 rounded-lg
                                                     bg-violet-50 px-2.5 py-1.5
                                                     text-xs font-semibold
                                                     text-violet-700">
                                            <x-engineermanagement-ui-icon
                                                name="skill"
                                                class="h-3.5 w-3.5"
                                            />
                                            Level:
                                            {{ $eng->getSkillLevelValue() }}
                                        </span>

                                    </div>

                                    @if ($eng->number)
                                        <p class="mt-3 break-words text-xs
                                                  text-gray-500">
                                            Reference:
                                            <span class="font-medium text-gray-700">
                                                {{ $eng->number }}
                                            </span>
                                        </p>
                                    @endif

                                </div>

                                {{-- Action --}}
                                <button
                                    type="button"
                                    wire:click="openEngineerSkill({{ $eng->id }}, {{ $eng->engineer_id }})"
                                    aria-label="Edit engineer skill"
                                    class="rounded-lg p-2 text-gray-400
                                           transition hover:bg-white
                                           hover:text-violet-600
                                           focus:outline-none focus:ring-2
                                           focus:ring-violet-500/30"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M7 17.5 17.5 7a2.12 2.12 0 0 1 3 3L10 20.5 5 22l2-4.5Z"/>
                                    </svg>
                                </button>

                            </div>
                        </article>

                    @empty

                        <div class="rounded-xl border border-dashed
                                    border-gray-200 px-5 py-12 text-center">

                            <div class="mx-auto flex h-12 w-12 items-center
                                        justify-center rounded-2xl bg-gray-100
                                        text-gray-400">
                                <x-engineermanagement-ui-icon
                                    name="user"
                                    class="h-6 w-6"
                                />
                            </div>

                            <h3 class="mt-4 text-sm font-semibold text-gray-900">
                                No engineers found
                            </h3>

                            <p class="mx-auto mt-1 max-w-sm text-sm
                                      leading-6 text-gray-500">
                                No engineers are currently associated with
                                this skill.
                            </p>

                        </div>

                    @endforelse

                </div>
            </section>

        </div>
    </div>

    {{-- Skill detail modal --}}
    @if ($displayEngineerSkillDetails)
        <livewire:addupdateengineerskillsmodal-component
            :originExternalForUpdate="true"
            :engineerSkillId="$engineerSkillId"
            :engineerId="$engineerId"
        />
    @endif

</div>
