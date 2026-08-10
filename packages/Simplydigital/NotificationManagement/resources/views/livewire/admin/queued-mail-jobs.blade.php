<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">
            Queued Mail Jobs
        </h1>

        <button
            wire:click="$refresh"
            class="px-4 py-2 border rounded"
        >
            Refresh
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b">
                <th class="text-left p-2">ID</th>
                <th class="text-left p-2">Queue</th>
                <th class="text-left p-2">Job</th>
                <th class="text-left p-2">Attempts</th>
                <th class="text-left p-2">Available</th>
                <th class="text-right p-2">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($jobs as $job)
                <tr class="border-b">
                    <td class="p-2">
                        {{ $job->id }}
                    </td>

                    <td class="p-2">
                        {{ $job->queue }}
                    </td>

                    <td class="p-2 text-sm">
                        {{ data_get(json_decode($job->payload, true), 'displayName') }}
                    </td>

                    <td class="p-2">
                        {{ $job->attempts }}
                    </td>

                    <td class="p-2">
                        {{ \Carbon\Carbon::createFromTimestamp($job->available_at)->toDateTimeString() }}
                    </td>

                    <td class="p-2 text-right">
                        <button
                            wire:click="runNow({{ $job->id }})"
                            wire:loading.attr="disabled"
                            wire:target="runNow({{ $job->id }})"
                            class="px-3 py-1 border rounded"
                        >
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
                    <td colspan="6" class="p-4 text-center">
                        No queued jobs.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $jobs->links() }}
    </div>
</div>