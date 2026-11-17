<section wire:poll.10s class="mt-3 space-y-4" aria-label="Manage SLA pause">
    <div>
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Pause or resume the SLA</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            These are the same manual SLA controls used in Case Management. AI does not choose to
            freeze or restart the clock. An authorised member of staff must explicitly take action.
        </p>
    </div>

    @if ($notice)
        <p role="status" class="rounded-lg border border-indigo-300 p-3 text-sm dark:border-indigo-600">
            {{ $notice }}
        </p>
    @endif

    @if (! $sla)
        <p class="rounded-lg border border-amber-300 p-3 text-sm text-amber-800 dark:text-amber-200">
            This case has no SLA task. Create it in the existing Manage SLA screen before using these controls.
        </p>
    @else
        @php
            $pause = $sla->activePause;
            $paused = $pause !== null;
            $timezone = config('app.timezone', 'UTC');
        @endphp
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-300 p-3 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SLA state</p>
                <p class="mt-1 font-semibold {{ $paused ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                    {{ $paused ? 'Paused — time frozen' : 'Not paused' }}
                </p>
                @if ($pause?->paused_at)
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        Paused at {{ $pause->paused_at->timezone($timezone)->format('d M Y, H:i:s') }} {{ $timezone }}
                    </p>
                @endif
            </div>
            <div class="rounded-lg border border-gray-300 p-3 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SLA deadlines</p>
                @if ($sla->respond_by)
                    <p class="mt-1 text-sm">Respond by: {{ $sla->respond_by->timezone($timezone)->format('d M Y, H:i') }}</p>
                @endif
                @if ($sla->fix_by)
                    <p class="mt-1 text-sm">Fix by: {{ $sla->fix_by->timezone($timezone)->format('d M Y, H:i') }}</p>
                @endif
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                    {{ $paused ? 'These stored deadlines are recalculated on Resume SLA; they are not a live countdown.' : 'Normal SLA calculation applies.' }}
                </p>
            </div>
        </div>

        @if (! $eligibility['allowed'])
            <p class="rounded-lg border border-amber-300 p-3 text-sm text-amber-800 dark:border-amber-700 dark:text-amber-200">
                {{ $eligibility['reason'] }}
            </p>
        @endif

        @can('update', $case)
            @if ($eligibility['allowed'])
                @if (! $paused)
                    <div class="space-y-3 rounded-lg border border-amber-300 p-4 dark:border-amber-700">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Pausing records the remaining response and fix allowance and marks this case as Paused.
                            Only pause when your operational SLA policy permits it.
                        </p>
                        <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <input type="checkbox" wire:model="confirmPause" class="mt-1">
                            <span>I confirm that this case is eligible to pause its SLA.</span>
                        </label>
                        @error('confirmPause') <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                        <button type="button" wire:click="pause" wire:loading.attr="disabled" wire:target="pause"
                            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="pause">Pause SLA</span>
                            <span wire:loading wire:target="pause">Pausing SLA…</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-3 rounded-lg border border-gray-300 p-4 dark:border-gray-700">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Resume SLA</strong> restarts the clock using the saved remaining allowance and
                            recalculates the response and fix deadlines. <strong>Cancel Pause</strong>
                            removes this pause without extending the original deadlines.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="resume" wire:loading.attr="disabled"
                                wire:target="resume,cancelPause"
                                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                                Resume SLA
                            </button>
                            <button type="button" wire:click="cancelPause" wire:loading.attr="disabled"
                                wire:target="resume,cancelPause"
                                wire:confirm="Cancel this SLA pause without extending the original deadlines?"
                                class="rounded-lg border border-red-500 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-300 disabled:opacity-60">
                                Cancel Pause
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        @else
            <p class="text-xs text-gray-500 dark:text-gray-400">You can view the SLA status, but need case update permission to pause or resume it.</p>
        @endcan
    @endif
</section>
