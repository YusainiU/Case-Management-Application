<div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm" data-planner-calendar>
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Planner</p>
            <h2 class="text-xl font-semibold text-slate-900">Event schedule</h2>
        </div>

        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
            {{ ucfirst($view ?? 'week') }}
        </span>
    </div>

    @forelse ($events as $event)
        <div class="mb-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $event['title'] ?? 'Untitled event' }}</p>
                    <p class="text-xs text-slate-500">{{ $event['type'] ?? 'meeting' }}</p>
                </div>

                <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-emerald-700">
                    {{ $event['status'] ?? 'scheduled' }}
                </span>
            </div>

            <div class="mt-2 space-y-1 text-xs text-slate-600">
                <p><strong>Starts:</strong> {{ data_get($event, 'starts_at') ?: data_get($event, 'startsAt') }}</p>
                <p><strong>Ends:</strong> {{ data_get($event, 'ends_at') ?: data_get($event, 'endsAt') }}</p>
                @if (! empty(data_get($event, 'location')))
                    <p><strong>Location:</strong> {{ data_get($event, 'location') }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
            No events scheduled for this time range.
        </div>
    @endforelse
</div>
