
@php
    $label = 'text-xs font-medium uppercase tracking-wider text-slate-500';
    $value = 'mt-1 text-sm font-semibold leading-6 text-slate-800 break-words';
@endphp

<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        {{-- Page heading --}}
        <div class="mb-8">
            <p class="text-sm font-medium text-indigo-600">
                ACCOUNT SETTINGS
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
               User Profile
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Manage user information and account security.
            </p>
        </div>

        {{-- Profile card --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200
                    bg-white shadow-sm">

            {{-- Profile header --}}
            <div class="flex flex-col gap-5 border-b border-slate-100
                        p-6 sm:flex-row sm:items-center sm:p-8">

                <div class="flex h-20 w-20 shrink-0 items-center
                            justify-center rounded-full bg-indigo-50
                            ring-4 ring-indigo-50/50">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="h-10 w-10 text-indigo-600">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold tracking-tight
                               text-slate-900">
                        {{ $user->name }}
                    </h2>

                    <p class="mt-1 break-all text-sm text-slate-500">
                        {{ $user->email }}
                    </p>

                    <div class="mt-3 inline-flex items-center gap-1.5
                                rounded-full bg-emerald-50 px-3 py-1
                                text-xs font-medium text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full
                                     bg-emerald-500"></span>
                        Personal account
                    </div>
                </div>

                <div class="hidden sm:block">
                    <span class="inline-flex items-center gap-2
                                 rounded-lg border border-slate-200
                                 px-3 py-2 text-sm text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5"
                             class="h-4 w-4">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M19.4 15a7.9 7.9 0 0 0 0-6l2-1.5-2-3.5-2.4 1a8 8 0 0 0-5.2-1.5L11 1H7l-.8 2.5A8 8 0 0 0 3 6L1 5.5l-1 4 2.2 1a8 8 0 0 0 0 3L0 15l1.8 3.5 2.4-1a8 8 0 0 0 5.2 1.5L10 21h4l.8-2.5a8 8 0 0 0 3.2-2.5l2 .5 1-4-1.6-1.5Z"
                                  transform="translate(1 1) scale(.9)"/>
                        </svg>
                        Account details
                    </span>
                </div>
            </div>

            {{-- Personal information --}}
            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Personal information
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Your registered contact and identification details.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    {{-- Address --}}
                    <div class="rounded-xl border border-slate-200
                                bg-slate-50/70 p-5 transition
                                hover:border-indigo-200">
                        <div class="{{ $label }}">Address</div>
                        <div class="{{ $value }}">
                            @if(!empty($address))
                                @foreach($address as $addr)
                                    @if($addr)
                                        <div>{{ $addr }}</div>
                                    @endif
                                @endforeach
                            @else
                                <span class="font-normal text-slate-400">
                                    Not provided
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="rounded-xl border border-slate-200
                                bg-slate-50/70 p-5 transition
                                hover:border-indigo-200">
                        <div class="{{ $label }}">Phone number</div>
                        <div class="{{ $value }}">
                            {{ $details->phone ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- Joined --}}
                    <div class="rounded-xl border border-slate-200
                                bg-slate-50/70 p-5 transition
                                hover:border-indigo-200">
                        <div class="{{ $label }}">Member since</div>
                        <div class="{{ $value }}">
                            {{ $user->created_at->format('F d, Y') }}
                        </div>
                    </div>

                    {{-- Identification --}}
                    <div class="rounded-xl border border-slate-200
                                bg-slate-50/70 p-5 transition
                                hover:border-indigo-200">
                        <div class="{{ $label }}">Identification</div>
                        <div class="{{ $value }}">
                            {{ $details->identification ?? 'N/A' }}
                        </div>
                    </div>

                </div>

                {{-- Security section --}}
                <div class="mt-8 flex flex-col gap-4 border-t
                            border-slate-100 pt-6 sm:flex-row
                            sm:items-center sm:justify-between">

                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center
                                    justify-center rounded-xl bg-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5"
                                 class="h-5 w-5 text-slate-600">
                                <rect x="4" y="10" width="16" height="11"
                                      rx="2"/>
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M8 10V7a4 4 0 1 1 8 0v3"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">
                                Password & security
                            </h3>
                            <p class="mt-1 max-w-sm text-sm text-slate-500">
                                Keep your account secure by updating your
                                password and profile details.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button"
                                wire:click="showEditingSection"
                                class="inline-flex items-center justify-center
                                       gap-2 rounded-xl bg-indigo-600
                                       px-5 py-3 text-sm font-semibold
                                       text-white shadow-sm transition
                                       hover:bg-indigo-700
                                       focus:outline-none focus:ring-2
                                       focus:ring-indigo-500
                                       focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 class="h-4 w-4">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="m16 4 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/>
                            </svg>
                            Edit details
                        </button>

                        <button type="button"
                                wire:click="showResetPasswordSection"
                                class="inline-flex items-center justify-center
                                       gap-2 rounded-xl border
                                       border-slate-200 bg-white
                                       px-5 py-3 text-sm font-semibold
                                       text-slate-700 transition
                                       hover:border-slate-300
                                       hover:bg-slate-50
                                       focus:outline-none focus:ring-2
                                       focus:ring-indigo-500
                                       focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 class="h-4 w-4">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M12 15v2m-7 4h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2Zm3-9V7a4 4 0 1 1 8 0v3"/>
                            </svg>
                            Reset password
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-5 text-center text-xs text-slate-400">
            Your account information is managed securely.
        </p>

    </div>
</div>
