
@php
    $ul = Config::get('engineer-management.listClass.ul');
    $li = Config::get('engineer-management.listClass.li');
    $liLeft = Config::get('engineer-management.listClass.liLeftItem');
    $liRightTop = Config::get('engineer-management.listClass.liRightTopItem');
    $liRightBottom = Config::get('engineer-management.listClass.liRightBottomItem');

    $containerClass = 'rounded-2xl border border-gray-200 bg-white shadow-[0_6px_0_0_rgba(15,23,42,0.025),0_10px_24px_-12px_rgba(15,23,42,0.12)] overflow-hidden';
    $headerClass = 'text-2xl font-bold tracking-tight text-gray-900';
    $headerClass2 = 'text-lg font-semibold tracking-tight text-gray-900';
    $ul = 'divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white';
    $ulSkill = $ul;
    $ulCertificate = $ul;
@endphp

<div class="w-full min-w-0">

    {{-- Main outer wrapper --}}
    <div class="{{ $containerClass }}">

        {{-- Engineer header --}}
        <div class="border-b border-gray-100 bg-white px-5 py-6
                    sm:px-7 sm:py-7">

            <div class="flex flex-col gap-5 sm:flex-row
                        sm:items-center sm:justify-between">

                <div class="flex min-w-0 items-center gap-4">

                    {{-- Profile avatar --}}
                    <div class="flex h-14 w-14 shrink-0 items-center
                                justify-center rounded-2xl bg-indigo-50
                                text-indigo-600 ring-1 ring-indigo-100">
                        <x-engineermanagement-ui-icon
                            name="left"
                            class="hidden"
                        />
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-7 w-7"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <circle cx="12" cy="8" r="4"/>
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="mb-1 text-xs font-semibold uppercase
                                  tracking-[0.15em] text-indigo-600">
                            Engineer Profile
                        </p>

                        <h1 class="{{ $headerClass }} break-words">
                            {{ $engineer->user->name }}
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Skills and professional certifications
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
                           focus:ring-indigo-500/10"
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

        {{-- Detail content --}}
        <div class="space-y-6 bg-gray-50/60 p-4 sm:p-6 lg:p-7">

            {{-- Skill and certificate counts --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div class="flex items-center gap-4 rounded-xl
                            border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex h-11 w-11 items-center justify-center
                                rounded-xl bg-violet-50 text-violet-600">
                        <x-engineermanagement-ui-icon
                            name="skill"
                            class="h-5 w-5"
                        />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Skills
                        </p>
                        <p class="mt-0.5 text-sm font-semibold text-gray-900">
                            {{ $engSkills->count() }}
                            {{ $engSkills->count() === 1 ? 'skill recorded' : 'skills recorded' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl
                            border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex h-11 w-11 items-center justify-center
                                rounded-xl bg-sky-50 text-sky-600">
                        <x-engineermanagement-ui-icon
                            name="certificate"
                            class="h-5 w-5"
                        />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Certifications
                        </p>
                        <p class="mt-0.5 text-sm font-semibold text-gray-900">
                            {{ $engCertifications->count() }}
                            {{ $engCertifications->count() === 1 ? 'certificate recorded' : 'certificates recorded' }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Skills and certifications --}}
            <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">

                {{-- Skills panel --}}
                <section class="min-w-0 overflow-hidden rounded-2xl
                                border border-gray-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b
                                border-gray-100 px-5 py-4">
                        <div class="flex h-10 w-10 items-center
                                    justify-center rounded-xl bg-violet-50
                                    text-violet-600">
                            <x-engineermanagement-ui-icon
                                name="skill"
                                class="h-5 w-5"
                            />
                        </div>
                        <div>
                            <h2 class="{{ $headerClass2 }}">Skills</h2>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Technical expertise and proficiency
                            </p>
                        </div>
                        <span class="ml-auto rounded-lg bg-gray-100
                                     px-2.5 py-1 text-xs font-semibold
                                     text-gray-600">
                            {{ $engSkills->count() }}
                        </span>
                    </div>

                    <div class="space-y-3 p-4">

                        @forelse ($engSkills as $skill)

                            <article
                                wire:key="engineer-skill-{{ $skill->id }}"
                                class="group rounded-xl border border-gray-200
                                       bg-white p-4 transition
                                       hover:border-violet-200 hover:bg-violet-50/30"
                            >
                                <div class="flex items-start gap-3">

                                    <button
                                        type="button"
                                        wire:click="openSkill({{ $skill->id }})"
                                        aria-label="View {{ $skill->skill->skills }}"
                                        class="flex h-10 w-10 shrink-0
                                               items-center justify-center
                                               rounded-xl bg-violet-50
                                               text-violet-600 transition
                                               hover:bg-violet-100
                                               focus:outline-none
                                               focus:ring-4
                                               focus:ring-violet-500/10"
                                    >
                                        <x-engineermanagement-ui-icon
                                            name="skill"
                                            class="h-5 w-5"
                                        />
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="break-words text-sm
                                                   font-semibold text-gray-900">
                                            {{ $skill->skill->skills }}
                                        </h3>

                                        <div class="mt-2 flex flex-wrap
                                                    items-center gap-2">
                                            <span class="inline-flex
                                                         items-center rounded-md
                                                         bg-violet-50 px-2 py-1
                                                         text-xs font-medium
                                                         text-violet-700">
                                                Level:
                                                &nbsp;{{ $skill->getSkillLevelValue() }}
                                            </span>
                                        </div>

                                        @if ($skill->number)
                                            <p class="mt-2 break-words
                                                      text-xs text-gray-500">
                                                {{ $skill->number }}
                                            </p>
                                        @endif
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="openSkill({{ $skill->id }})"
                                        aria-label="Edit skill"
                                        class="rounded-lg p-2 text-gray-400
                                               transition hover:bg-white
                                               hover:text-violet-600
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-violet-500/30"
                                    >
                                        <svg class="h-4 w-4"
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
                                        border-gray-200 px-5 py-10 text-center">
                                <div class="mx-auto flex h-11 w-11
                                            items-center justify-center
                                            rounded-xl bg-gray-100
                                            text-gray-400">
                                    <x-engineermanagement-ui-icon
                                        name="skill"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-700">
                                    No skills recorded
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Skills for this engineer will appear here.
                                </p>
                            </div>

                        @endforelse

                    </div>
                </section>

                {{-- Certifications panel --}}
                <section class="min-w-0 overflow-hidden rounded-2xl
                                border border-gray-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b
                                border-gray-100 px-5 py-4">
                        <div class="flex h-10 w-10 items-center
                                    justify-center rounded-xl bg-sky-50
                                    text-sky-600">
                            <x-engineermanagement-ui-icon
                                name="certificate"
                                class="h-5 w-5"
                            />
                        </div>
                        <div>
                            <h2 class="{{ $headerClass2 }}">
                                Certifications
                            </h2>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Credentials and certification status
                            </p>
                        </div>
                        <span class="ml-auto rounded-lg bg-gray-100
                                     px-2.5 py-1 text-xs font-semibold
                                     text-gray-600">
                            {{ $engCertifications->count() }}
                        </span>
                    </div>

                    <div class="space-y-3 p-4">

                        @forelse ($engCertifications as $cert)

                            @php
                                $expired = $cert->isExpired() ?? false;
                            @endphp

                            <article
                                wire:key="engineer-certificate-{{ $cert->id }}"
                                class="group rounded-xl border border-gray-200
                                       bg-white p-4 transition
                                       hover:border-sky-200 hover:bg-sky-50/30"
                            >
                                <div class="flex items-start gap-3">

                                    <button
                                        type="button"
                                        wire:click="openCertificate({{ $cert->id }})"
                                        aria-label="View {{ $cert->certificate->name }}"
                                        class="flex h-10 w-10 shrink-0
                                               items-center justify-center
                                               rounded-xl bg-sky-50
                                               text-sky-600 transition
                                               hover:bg-sky-100
                                               focus:outline-none
                                               focus:ring-4
                                               focus:ring-sky-500/10"
                                    >
                                        <x-engineermanagement-ui-icon
                                            name="certificate"
                                            class="h-5 w-5"
                                        />
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap
                                                    items-center gap-2">
                                            <h3 class="break-words text-sm
                                                       font-semibold text-gray-900">
                                                {{ $cert->certificate->name }}
                                            </h3>

                                            @if ($expired)
                                                <span class="inline-flex
                                                             items-center gap-1
                                                             rounded-full
                                                             bg-rose-50 px-2 py-1
                                                             text-[11px]
                                                             font-semibold
                                                             text-rose-700
                                                             ring-1 ring-inset
                                                             ring-rose-100">
                                                    <span class="h-1.5 w-1.5
                                                                 rounded-full
                                                                 bg-rose-500"></span>
                                                    Expired
                                                </span>
                                            @else
                                                <span class="inline-flex
                                                             items-center gap-1
                                                             rounded-full
                                                             bg-emerald-50 px-2 py-1
                                                             text-[11px]
                                                             font-semibold
                                                             text-emerald-700
                                                             ring-1 ring-inset
                                                             ring-emerald-100">
                                                    <span class="h-1.5 w-1.5
                                                                 rounded-full
                                                                 bg-emerald-500"></span>
                                                    Not expired
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-2 text-xs font-medium
                                                  text-gray-600">
                                            {{ $cert->certificate->getTypeValue() }}
                                        </p>

                                        @if ($cert->certificate->number)
                                            <p class="mt-2 break-words
                                                      text-xs text-gray-500">
                                                Certificate no.:
                                                {{ $cert->certificate->number }}
                                            </p>
                                        @endif
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="openCertificate({{ $cert->id }})"
                                        aria-label="Edit certification"
                                        class="rounded-lg p-2 text-gray-400
                                               transition hover:bg-white
                                               hover:text-sky-600
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-sky-500/30"
                                    >
                                        <svg class="h-4 w-4"
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
                                        border-gray-200 px-5 py-10 text-center">
                                <div class="mx-auto flex h-11 w-11
                                            items-center justify-center
                                            rounded-xl bg-gray-100
                                            text-gray-400">
                                    <x-engineermanagement-ui-icon
                                        name="certificate"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-700">
                                    No certifications recorded
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Certifications for this engineer will appear here.
                                </p>
                            </div>

                        @endforelse

                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- Certificate modal --}}
    @if ($openCertificateModal)
        <livewire:addupdateengineercertificatesmodal-component
            :engineerCertificateId="$selectedEngineerCertificateId"
            :engineerId="$engineerId"
            :originExternalForUpdate="true"
        />
    @endif

    {{-- Skill modal --}}
    @if ($openSkillModal)
        <livewire:addupdateengineerskillsmodal-component
            :engineerSkillId="$selectedEngineerSkillId"
            :engineerId="$engineerId"
            :originExternalForUpdate="true"
        />
    @endif

</div>
