
@php
    $allEngineers = "allEngineers";
    $allCertificates = "allCertificates";
    $allSkills = "allSkills";
    $expiredCertifications = "expiredCertifications";
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="min-h-full p-3 sm:p-6 lg:p-8">

        {{-- Dashboard outer wrapper --}}
        <div class="mx-auto w-full max-w-7xl overflow-hidden
                    rounded-2xl border border-gray-200 bg-white
                    shadow-[0_8px_0_0_rgba(15,23,42,0.03),0_12px_30px_-12px_rgba(15,23,42,0.12)]">

            {{-- Dashboard header --}}
            <div class="border-b border-gray-100 px-5 py-6 sm:px-8 sm:py-7">
                <div class="flex flex-col gap-4 sm:flex-row
                            sm:items-center sm:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            <span class="text-xs font-semibold uppercase
                                         tracking-[0.16em] text-indigo-600">
                                Engineer Management
                            </span>
                        </div>

                        <h1 class="text-2xl font-bold tracking-tight
                                   text-gray-900 sm:text-3xl">
                            Dashboard
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                            Overview of your engineering workforce,
                            certifications and technical skills.
                        </p>
                    </div>

                    <div class="inline-flex w-fit items-center gap-2 rounded-xl
                                border border-gray-200 bg-gray-50 px-3.5 py-2.5
                                text-sm font-medium text-gray-600">
                        <svg class="h-4 w-4 text-gray-400"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                        Workforce overview
                    </div>
                </div>
            </div>

            {{-- Dashboard content --}}
            <div class="space-y-8 p-5 sm:p-8">

                {{-- KPI section --}}
                <section>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">
                                Key metrics
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Your workforce at a glance.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2
                                xl:grid-cols-4">

                        {{-- Total engineers --}}
                        <div class="group relative overflow-hidden rounded-2xl
                                    border border-gray-200 bg-white p-5
                                    shadow-sm transition duration-200
                                    hover:-translate-y-0.5 hover:shadow-md">
                            <div class="absolute inset-x-0 top-0 h-1
                                        bg-indigo-500"></div>

                            <div class="mb-5 flex items-center justify-between">
                                <div class="flex h-11 w-11 items-center
                                            justify-center rounded-xl
                                            bg-indigo-50 text-indigo-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="10" cy="7" r="4"/>
                                        <path stroke-linecap="round"
                                              d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </div>
                                <span class="rounded-full bg-indigo-50 px-2.5
                                             py-1 text-xs font-medium
                                             text-indigo-700">
                                    Workforce
                                </span>
                            </div>

                            <p class="text-sm font-medium text-gray-500">
                                Total engineers
                            </p>
                            <div class="mt-2 text-gray-900">
                                <livewire:engineer-management-counter
                                    :selectCounter="$allEngineers"
                                    :key="'dashboard-all-engineers'"
                                />
                            </div>
                            <p class="mt-3 text-xs text-gray-400">
                                Registered engineering personnel
                            </p>
                        </div>

                        {{-- Total certificates --}}
                        <div class="group relative overflow-hidden rounded-2xl
                                    border border-gray-200 bg-white p-5
                                    shadow-sm transition duration-200
                                    hover:-translate-y-0.5 hover:shadow-md">
                            <div class="absolute inset-x-0 top-0 h-1
                                        bg-sky-500"></div>

                            <div class="mb-5 flex items-center justify-between">
                                <div class="flex h-11 w-11 items-center
                                            justify-center rounded-xl
                                            bg-sky-50 text-sky-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linejoin="round"
                                              d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                                        <path stroke-linejoin="round"
                                              d="M14 3v6h5M9 14h6M9 17h6"/>
                                    </svg>
                                </div>
                                <span class="rounded-full bg-sky-50 px-2.5
                                             py-1 text-xs font-medium text-sky-700">
                                    Credentials
                                </span>
                            </div>

                            <p class="text-sm font-medium text-gray-500">
                                Total certificates
                            </p>
                            <div class="mt-2 text-gray-900">
                                <livewire:engineer-management-counter
                                    :selectCounter="$allCertificates"
                                    :key="'dashboard-all-certificates'"
                                />
                            </div>
                            <p class="mt-3 text-xs text-gray-400">
                                Certificates recorded in the system
                            </p>
                        </div>

                        {{-- Total skills --}}
                        <div class="group relative overflow-hidden rounded-2xl
                                    border border-gray-200 bg-white p-5
                                    shadow-sm transition duration-200
                                    hover:-translate-y-0.5 hover:shadow-md">
                            <div class="absolute inset-x-0 top-0 h-1
                                        bg-violet-500"></div>

                            <div class="mb-5 flex items-center justify-between">
                                <div class="flex h-11 w-11 items-center
                                            justify-center rounded-xl
                                            bg-violet-50 text-violet-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                    </svg>
                                </div>
                                <span class="rounded-full bg-violet-50 px-2.5
                                             py-1 text-xs font-medium
                                             text-violet-700">
                                    Expertise
                                </span>
                            </div>

                            <p class="text-sm font-medium text-gray-500">
                                Total skills
                            </p>
                            <div class="mt-2 text-gray-900">
                                <livewire:engineer-management-counter
                                    :selectCounter="$allSkills"
                                    :key="'dashboard-all-skills'"
                                />
                            </div>
                            <p class="mt-3 text-xs text-gray-400">
                                Technical skills in your skills library
                            </p>
                        </div>

                        {{-- Expired certifications --}}
                        <div class="group relative overflow-hidden rounded-2xl
                                    border border-gray-200 bg-white p-5
                                    shadow-sm transition duration-200
                                    hover:-translate-y-0.5 hover:shadow-md">
                            <div class="absolute inset-x-0 top-0 h-1
                                        bg-rose-500"></div>

                            <div class="mb-5 flex items-center justify-between">
                                <div class="flex h-11 w-11 items-center
                                            justify-center rounded-xl
                                            bg-rose-50 text-rose-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor"
                                         stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round"
                                              d="M12 7v5l3 2"/>
                                    </svg>
                                </div>
                                <span class="rounded-full bg-rose-50 px-2.5
                                             py-1 text-xs font-medium
                                             text-rose-700">
                                    Attention
                                </span>
                            </div>

                            <p class="text-sm font-medium text-gray-500">
                                Expired certifications
                            </p>
                            <div class="mt-2 text-gray-900">
                                <livewire:engineer-management-counter
                                    :selectCounter="$expiredCertifications"
                                    :key="'dashboard-expired-certifications'"
                                />
                            </div>
                            <p class="mt-3 text-xs text-gray-400">
                                Certifications requiring review
                            </p>
                        </div>

                    </div>
                </section>

                {{-- Engineers summary --}}
                <section class="overflow-hidden rounded-2xl border
                                border-gray-200 bg-white shadow-sm">

                    <div class="flex flex-col gap-3 border-b border-gray-100
                                bg-gray-50/70 px-5 py-5 sm:flex-row
                                sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">
                                Engineers overview
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Review engineer records and workforce details.
                            </p>
                        </div>

                        <div class="inline-flex w-fit items-center gap-2
                                    rounded-lg border border-gray-200 bg-white
                                    px-3 py-2 text-xs font-medium text-gray-500">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Live data
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <livewire:engineers-summary />
                    </div>

                </section>

            </div>
        </div>
    </div>
</x-layouts.app>
