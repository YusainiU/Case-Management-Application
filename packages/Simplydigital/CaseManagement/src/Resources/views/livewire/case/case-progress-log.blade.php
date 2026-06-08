<div>
    <div class="flow-root">
        <ul role="list" class="-mb-8">

            @foreach ($taskLogs as $log)
                <li>
                    <div class="relative pb-8">

                        @if (!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        @endif

                        <div class="relative flex space-x-3">

                            <!-- Timeline Dot -->
                            <div>
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 ring-8 ring-white">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="min-w-0 flex-1">
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">

                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                {{ $log->type }}
                                            </p>

                                            <p class="text-sm text-gray-500">
                                                {{ $log->user?->name ?? 'System' }}
                                            </p>
                                        </div>

                                        <time class="text-sm text-gray-500">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </time>
                                    </div>

                                    @if ($log->description)
                                        <div class="mt-3 text-sm text-gray-700">
                                            {{ $log->description }}
                                        </div>
                                    @endif

                                    @if ($log->old_value || $log->new_value)
                                        <div class="mt-4 rounded bg-gray-50 p-3 text-sm">
                                            <div>
                                                <span class="font-medium text-red-600">From:</span>
                                                {{ $log->old_value }}
                                            </div>

                                            <div class="mt-1">
                                                <span class="font-medium text-green-600">To:</span>
                                                {{ $log->new_value }}
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </li>
            @endforeach

        </ul>
    </div>
</div>
