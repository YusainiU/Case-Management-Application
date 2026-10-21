<div class="min-w-0 space-y-6 bg-slate-50 p-4 text-slate-900 sm:p-6">

    {{-- Page header --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                            bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m4.5 6 7.5 6 7.5-6" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">
                        System Administration
                    </p>

                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                        Queued Mail Jobs
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm leading-5 text-slate-500">
                        Monitor pending email jobs and manually trigger processing when needed.
                    </p>
                </div>
            </div>

            <button
                type="button"
                wire:click="$refresh"
                wire:loading.attr="disabled"
                wire:target="$refresh"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg
                       border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold
                       text-slate-700 shadow-sm transition hover:bg-slate-50
                       hover:text-slate-900 disabled:cursor-wait disabled:opacity-60
                       focus:outline-none focus-visible:ring-2
                       focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
            >
                <svg
                    wire:loading.remove
                    wire:target="$refresh"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7v5h-5M4 17v-5h5" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5.5 9A7 7 0 0117.3 6.7L20 12M4 12l2.7 5.3A7 7 0 0018.5 15" />
                </svg>

                <svg
                    wire:loading
                    wire:target="$refresh"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="9"
                            stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor"
                          d="M12 3a9 9 0 019 9h-3a6 6 0 00-6-6V3z" />
                </svg>

                <span wire:loading.remove wire:target="$refresh">Refresh</span>
                <span wire:loading wire:target="$refresh">Refreshing...</span>
            </button>

        </div>
    </section>

    {{-- Flash messages --}}
    @if (session('success'))
        <div
            role="status"
            class="flex items-start gap-3 rounded-xl border border-emerald-200
                   bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75 11.25 15 15.75 9.75" />
                <circle cx="12" cy="12" r="9" />
            </svg>

            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div
            role="alert"
            class="flex items-start gap-3 rounded-xl border border-rose-200
                   bg-rose-50 px-4 py-3 text-sm text-rose-800"
        >
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 8v4m0 4h.01" />
            </svg>

            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Jobs table --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row
                    sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Mail queue
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Review queued jobs and their processing status.
                </p>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-full
                         bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700
                         ring-1 ring-inset ring-indigo-100">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                {{ $jobs->total() }} {{ $jobs->total() === 1 ? 'job' : 'jobs' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Job ID</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Queue</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Job details</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Attempts</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Available at</th>
                        <th scope="col" class="px-5 py-3.5 text-right font-semibold">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($jobs as $job)
                        @php
                            $payload = json_decode($job->payload, true);
                            $jobName = data_get($payload, 'displayName');
                        @endphp

                        <tr wire:key="queued-mail-job-{{ $job->id }}"
                            class="transition-colors hover:bg-slate-50/80">

                            {{-- ID --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="font-mono text-xs font-semibold text-slate-600">
                                    #{{ $job->id }}
                                </span>
                            </td>

                            {{-- Queue --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-100
                                             px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $job->queue ?: 'default' }}
                                </span>
                            </td>

                            {{-- Job details --}}
                            <td class="max-w-xs px-5 py-4">
                                <p class="truncate font-medium text-slate-800"
                                   title="{{ $jobName ?: 'Unknown job' }}">
                                    {{ $jobName ?: 'Unknown job' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Queued email task
                                </p>
                            </td>

                            {{-- Attempts --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <span @class([
                                    'inline-flex min-w-8 items-center justify-center rounded-full px-2 py-1 text-xs font-semibold',
                                    'bg-emerald-50 text-emerald-700' => (int) $job->attempts === 0,
                                    'bg-amber-50 text-amber-700' => (int) $job->attempts > 0,
                                ])>
                                    {{ $job->attempts }}
                                </span>
                            </td>

                            {{-- Availability --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm text-slate-700">
                                    {{ \Carbon\Carbon::createFromTimestamp($job->available_at)->format('d M Y') }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ \Carbon\Carbon::createFromTimestamp($job->available_at)->format('H:i:s') }}
                                </p>
                            </td>

                            {{-- Action --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="runNow({{ $job->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="runNow({{ $job->id }})"
                                    class="inline-flex min-w-24 items-center justify-center gap-2
                                           rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold
                                           text-white shadow-sm transition hover:bg-indigo-700
                                           disabled:cursor-wait disabled:opacity-60
                                           focus:outline-none focus-visible:ring-2
                                           focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                                >
                                    <svg
                                        wire:loading.remove
                                        wire:target="runNow({{ $job->id }})"
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m9 6 9 6-9 6V6z" />
                                    </svg>

                                    <svg
                                        wire:loading
                                        wire:target="runNow({{ $job->id }})"
                                        class="h-3.5 w-3.5 animate-spin"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="9"
                                                stroke="currentColor" stroke-width="3" />
                                        <path class="opacity-75" fill="currentColor"
                                              d="M12 3a9 9 0 019 9h-3a6 6 0 00-6-6V3z" />
                                    </svg>

                                    <span wire:loading.remove wire:target="runNow({{ $job->id }})">
                                        Run now
                                    </span>
                                    <span wire:loading wire:target="runNow({{ $job->id }})">
                                        Running...
                                    </span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-12 w-12 items-center justify-center
                                                rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3.75 6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m4.5 6 7.5 6 7.5-6" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                        No queued mail jobs
                                    </h3>

                                    <p class="mt-1 text-sm leading-6 text-slate-500">
                                        There are currently no email jobs waiting in the queue.
                                        Refresh to check again.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($jobs->hasPages())
            <div class="border-t border-slate-200 bg-white px-4 py-4 sm:px-5">
                {{ $jobs->links() }}
            </div>
        @endif

    </section>

</div>
