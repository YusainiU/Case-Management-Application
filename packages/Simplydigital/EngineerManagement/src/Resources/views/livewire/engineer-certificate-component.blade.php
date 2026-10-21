
@php
    $containerClass = 'p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/60';
    $headerClass = 'text-2xl sm:text-3xl font-bold tracking-tight text-slate-800';
    $headerClass2 = 'text-lg font-semibold text-slate-800';
@endphp

<div class="space-y-6 p-4">

    {{-- Certification Header --}}
    <div class="{{ $containerClass }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-engineermanagement-ui-icon
                        name="certificate"
                        class="h-6 w-6"
                    />
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-violet-600">
                        Certification Details
                    </p>
                    <h1 class="{{ $headerClass }} break-words">
                        {{ $certificate->name }}
                    </h1>
                </div>
            </div>

            <button
                type="button"
                wire:click="closeComponent"
                class="inline-flex items-center justify-center gap-2 self-start sm:self-auto rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2"
            >
                <x-engineermanagement-ui-icon
                    name="left"
                    class="h-4 w-4"
                />
                Back to List
            </button>
        </div>
    </div>

    {{-- Edit Certification Modal --}}
    @if ($displayEngineerCertificateDetails)
        <livewire:addupdateengineercertificatesmodal-component
            :originExternalForUpdate="true"
            :engineerCertificateId="$engineerCertificateId"
            :engineerId="$engineerId"
            @close-modal="refreshEngineerCertifications"
        />
    @endif

    {{-- Engineers Holding This Certification --}}
    @if ($engineerWithCertificates->isNotEmpty())
        <div class="space-y-4">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                <div>
                    <h2 class="{{ $headerClass2 }}">
                        Engineers with Selected Certification
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        View certification dates, issuing authority, and certificate numbers.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full bg-violet-50 px-3 py-1 text-sm font-semibold text-violet-700">
                    {{ $engineerWithCertificates->count() }}
                    {{ $engineerWithCertificates->count() === 1 ? 'Engineer' : 'Engineers' }}
                </span>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @foreach ($engineerWithCertificates as $eng)
                    <div
                        wire:key="engineer-certificate-{{ $eng->id }}"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/40 transition duration-200 hover:border-violet-200 hover:shadow-md"
                    >
                        {{-- Engineer Identity --}}
                        <div class="flex items-start gap-3">
                            <button
                                type="button"
                                wire:click="openEngineerCertificate({{ $eng->id }}, {{ $eng->engineer_id }})"
                                aria-label="View certification details for {{ $eng->engineer->user->name }}"
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2"
                            >
                                <x-engineermanagement-ui-icon
                                    name="certificate"
                                    class="h-5 w-5"
                                />
                            </button>

                            <div class="min-w-0 flex-1">
                                <button
                                    type="button"
                                    wire:click="openEngineerCertificate({{ $eng->id }}, {{ $eng->engineer_id }})"
                                    class="text-left font-semibold text-slate-800 transition hover:text-violet-700 focus:outline-none focus:underline"
                                >
                                    {{ $eng->engineer->user->name }}
                                </button>

                                <p class="mt-1 text-xs text-slate-500">
                                    Certificate Number
                                </p>
                                <p class="break-all text-sm font-medium text-slate-700">
                                    {{ $eng->certification_number ?: 'Not provided' }}
                                </p>
                            </div>

                            @if ($eng->isExpired())
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Expired
                                </span>
                            @else
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Valid
                                </span>
                            @endif
                        </div>

                        {{-- Certification Information --}}
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">

                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-xs font-medium text-slate-500">
                                    Issued Date
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $eng->formatIssuedDate() ?: 'Not provided' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-xs font-medium text-slate-500">
                                    Expiry Date
                                </p>
                                <p class="mt-1 text-sm font-semibold {{ $eng->isExpired() ? 'text-red-600' : 'text-slate-800' }}">
                                    {{ $eng->formatExpiryDate() ?: 'Not provided' }}
                                </p>
                            </div>

                        </div>

                        {{-- Issuing Authority --}}
                        <div class="mt-3 flex items-start gap-2 border-t border-slate-100 pt-4">
                            <x-engineermanagement-ui-icon
                                name="certificate"
                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                            />

                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">
                                    Issued By
                                </p>
                                <p class="mt-1 text-sm text-slate-700">
                                    {{ $eng->certificate->issued_by ?: 'Not specified' }}
                                </p>
                            </div>
                        </div>

                        {{-- View Details --}}
                        <div class="mt-4 border-t border-slate-100 pt-3">
                            <button
                                type="button"
                                wire:click="openEngineerCertificate({{ $eng->id }}, {{ $eng->engineer_id }})"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-violet-700 transition hover:text-violet-900 focus:outline-none focus:underline"
                            >
                                View certification details
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <x-engineermanagement-ui-icon
                    name="certificate"
                    class="h-7 w-7"
                />
            </div>

            <h2 class="mt-4 text-base font-semibold text-slate-800">
                No engineers found
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                No engineers currently have this certification assigned.
                Engineers with this certification will appear here.
            </p>

            <button
                type="button"
                wire:click="closeComponent"
                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2"
            >
                <x-engineermanagement-ui-icon
                    name="left"
                    class="h-4 w-4"
                />
                Back to List
            </button>
        </div>
    @endif

</div>
