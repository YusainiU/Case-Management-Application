@php
    use Carbon\Carbon;
@endphp

<div class="w-full">

    @if($showEventModal && $case)
        <livewire:manage-case :case="$case" />
    @endif

    {{-- Calendar header --}}
    <div class="flex items-center justify-between mb-4 mt-7 ml-4 mr-3">

        <div>
            <h2 class="text-xl font-semibold">
                {{ Carbon::parse($date)->format('F Y') }}
            </h2>
        </div>

        <div class="flex gap-2">
            <button type="button" wire:click="previous" class="px-3 py-2 border rounded">
                Previous
            </button>

            <button type="button" wire:click="today" class="px-3 py-2 border rounded">
                Today
            </button>

            <button type="button" wire:click="next" class="px-3 py-2 border rounded">
                Next
            </button>
        </div>

    </div>


    {{-- Day names --}}
    <div class="grid grid-cols-7 border-l border-b border-gray-500">

        @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
            <div class="p-3 text-sm text-gray-700 font-semibold bg-gray-100 border-r border-b">
                {{ $day }}
            </div>
        @endforeach

    </div>

    {{-- Calendar days --}}
    <div class="grid grid-cols-7 border-l">

        @foreach ($calendarDays as $day)
            @php
                $dateKey = $day['date']->format('Y-m-d');

                $dayEvents = collect($events)->filter(function ($event) use ($dateKey) {
                    return Carbon::parse($event['start'])->format('Y-m-d') === $dateKey;
                });
            @endphp

            <div
                class="
                    min-h-35
                    p-2
                    border-r
                    border-b
                    border-gray-500
                    bg-white
                    {{ $day['isCurrentMonth'] ? '' : 'bg-gray-50 text-gray-400' }}
                ">

                <div class="flex justify-between mb-2">

                    <span
                        class="
                            flex items-center justify-center
                            w-7 h-7
                            text-sm
                            rounded-full
                            {{ $day['isToday'] ? 'bg-blue-600 text-white font-bold' : 'text-gray-700' }}
                        ">
                        {{ $day['date']->day }}
                    </span>

                </div>

                <div class="space-y-1">

                    @forelse ($events as $event)
                        <div class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 truncate">
                            @if (Carbon::parse($event['start'])->toDateString() === $day['date']->toDateString())
                                <a href="#" 
                                    class="block px-2 py-2"
                                    style="background-color: {{ $event['colour'] ?? null }}; font-weight: bold;" 
                                    wire:click.prevent="openEventModal({{ $event['id'] }})"                                    
                                >
                                    <div class="font-medium truncate" style="color: {{ $event['text'] ?? null }};">
                                        {{ $event['title'] }}
                                    </div>
                                    <div class="text-[10px]" style="color: {{ $event['text'] ?? null }};">
                                        {{ Carbon::parse($event['start'])->format('H:i') }}
                                    </div>
                                </a>                                
                            @endif
                        </div>
                    @empty
                        <div>&nbsp;</div>
                    @endforelse
                </div>

            </div>
        @endforeach

    </div>

</div>
