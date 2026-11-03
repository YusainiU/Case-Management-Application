
@php
    use Carbon\Carbon;

    $calendarMonth = Carbon::parse($date);
    $today = Carbon::today()->toDateString();

    $eventsByDate = collect($events)->groupBy(function ($event) {
        return Carbon::parse($event['start'])->toDateString();
    });
@endphp

<div class="w-full space-y-5 bg-slate-50 p-4 text-slate-900 sm:p-6 dark:bg-gray-950 dark:text-slate-100">

    {{-- Event modal --}}
    @if ($showEventModal && $case)
        <livewire:manage-case :case="$case" @close-profile-modal="closeEventModal"/>
    @endif

    {{-- Calendar toolbar --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                    Schedule
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    Event Planner
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    View and manage your scheduled events.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="previous"
                    aria-label="Previous month"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200
                           bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm
                           transition hover:bg-slate-50 hover:text-slate-900
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                           dark:border-slate-700 dark:bg-gray-800 dark:text-slate-200
                           dark:hover:bg-gray-700 dark:hover:text-white"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m15 18-6-6 6-6" />
                    </svg>
                    Previous
                </button>

                <button
                    type="button"
                    wire:click="today"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                           text-white shadow-sm transition hover:bg-indigo-700
                           focus:outline-none focus-visible:ring-2
                           focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                           dark:focus-visible:ring-offset-gray-900"
                >
                    Today
                </button>

                <button
                    type="button"
                    wire:click="next"
                    aria-label="Next month"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200
                           bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm
                           transition hover:bg-slate-50 hover:text-slate-900
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                           dark:border-slate-700 dark:bg-gray-800 dark:text-slate-200
                           dark:hover:bg-gray-700 dark:hover:text-white"
                >
                    Next
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>

        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold text-slate-900 sm:text-xl dark:text-white">
                {{ $calendarMonth->format('F Y') }}
            </h2>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                Today
                <span class="ml-2 h-2.5 w-2.5 rounded-full bg-slate-200 dark:bg-slate-700"></span>
                Other dates
            </div>
        </div>
    </section>

    {{-- Calendar --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-gray-900">

        {{-- Horizontal scrolling keeps the seven-column layout usable on mobile --}}
        <div class="overflow-x-auto">
            <div class="min-w-[840px]">

                {{-- Weekday headings --}}
                <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-gray-800">
                    @foreach ([
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                        'Sunday',
                    ] as $dayName)
                        <div
                            class="border-r border-slate-200 px-3 py-3 text-center
                                   text-xs font-semibold uppercase tracking-wide text-slate-500
                                   dark:border-slate-700 dark:text-slate-400
                                   last:border-r-0"
                        >
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar days --}}
                <div class="grid grid-cols-7">
                    @foreach ($calendarDays as $day)
                        @php
                            $dateKey = $day['date']->toDateString();
                            $dayEvents = $eventsByDate->get($dateKey, collect());
                        @endphp

                        <div
                            wire:key="calendar-day-{{ $dateKey }}"
                            @class([
                                'flex min-h-36 flex-col border-b border-r border-slate-200 p-2.5 dark:border-slate-800',
                                'bg-white dark:bg-gray-900' => $day['isCurrentMonth'],
                                'bg-slate-50/80 dark:bg-gray-800/60' => ! $day['isCurrentMonth'],
                                'border-r-0' => $loop->iteration % 7 === 0,
                            ])
                        >
                            {{-- Date number --}}
                            <div class="mb-2 flex items-center justify-between">
                                <span
                                    @class([
                                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                                        'bg-indigo-600 text-white shadow-sm' => $day['isToday'],
                                        'text-slate-800 dark:text-slate-200' => $day['isCurrentMonth'] && ! $day['isToday'],
                                        'text-slate-400 dark:text-slate-500' => ! $day['isCurrentMonth'],
                                    ])
                                >
                                    {{ $day['date']->day }}
                                </span>

                                @if ($dayEvents->isNotEmpty())
                                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">
                                        {{ $dayEvents->count() }}
                                        {{ $dayEvents->count() === 1 ? 'event' : 'events' }}
                                    </span>
                                @endif
                            </div>

                            {{-- Events for this date --}}
                            <div class="min-w-0 space-y-1.5">
                                @foreach ($dayEvents as $event)
                                    @php
                                        $eventStart = Carbon::parse($event['start']);
                                        $eventColour = $event['colour'] ?? '#EEF2FF';
                                        $eventText = $event['text'] ?? '#3730A3';
                                    @endphp

                                    <button
                                        type="button"
                                        wire:key="calendar-event-{{ $event['id'] }}-{{ $dateKey }}"
                                        wire:click="openEventModal({{ $event['id'] }})"
                                        title="{{ $event['title'] }} — {{ $eventStart->format('H:i') }}"
                                        style="background-color: {{ $eventColour }}; color: {{ $eventText }}; border-left-color: {{ $eventText }};"
                                        class="block w-full min-w-0 rounded-md border-l-[3px] px-2 py-1.5
                                               text-left transition hover:brightness-95
                                               focus:outline-none focus-visible:ring-2
                                               focus-visible:ring-indigo-500"
                                    >
                                        <span class="block truncate text-xs font-semibold">
                                            {{ $event['title'] }}
                                        </span>

                                        <span class="mt-0.5 flex items-center gap-1 text-[10px] font-medium opacity-80">
                                            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24"
                                                 fill="none" stroke="currentColor" stroke-width="2"
                                                 aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" />
                                                <path stroke-linecap="round"
                                                      d="M12 7v5l3 2" />
                                            </svg>
                                            {{ $eventStart->format('H:i') }}
                                        </span>
                                    </button>
                                @endforeach

                                @if ($dayEvents->isEmpty())
                                    <div class="py-2 text-center text-xs text-slate-300 dark:text-slate-600">
                                        —
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Calendar footer --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200
                    bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-gray-800">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Select an event to view its details.
            </p>

            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ count($calendarDays) }} calendar days
            </p>
        </div>
    </section>

</div>
