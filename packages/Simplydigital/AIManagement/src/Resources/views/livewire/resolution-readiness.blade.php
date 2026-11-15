<section class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="Resolution readiness review">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Resolution Readiness — Staff Handoff</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Prepare evidence for a possible manual resolution. AI suggestions are not proof that a fix succeeded.
            Saving this handoff does not change case state, resolve or close the case, update SLA records,
            or notify the customer.
        </p>
    </div>

    @if ($notice)
        <p role="status" class="rounded-md border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif

    @can('update', $case)
        @if (! $case->resolved_datetime && ! $case->closed_datetime && in_array($case->state, \Simplydigital\CaseManagement\Enums\CaseState::openStates(), true))
            @if ($troubleshootingNotes->isNotEmpty())
                <div class="space-y-3 rounded-lg border border-gray-300 p-4 dark:border-gray-700">
                    <label for="resolution-source-note" class="block text-sm font-medium">
                        Reviewed troubleshooting note
                    </label>
                    <select id="resolution-source-note" wire:model="sourceLogId"
                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Select a reviewed note</option>
                        @foreach ($troubleshootingNotes as $note)
                            <option value="{{ $note->id }}">
                                #{{ $note->number }} · {{ $note->created_at?->format('d M Y, H:i') }}
                                · {{ $note->user?->name ?? 'Staff reviewer' }}
                            </option>
                        @endforeach
                    </select>
                    @error('sourceLogId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <button type="button" wire:click="prepare" wire:loading.attr="disabled" wire:target="prepare"
                        class="rounded-md border border-indigo-500 px-3 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                        Prepare resolution handoff
                    </button>

                    @if ($snapshotHash !== null)
                        <div class="space-y-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                            <p class="text-sm font-medium">
                                Enter verified work performed, not AI guesses or planned checks.
                            </p>
                            <label for="resolution-fix" class="block text-sm font-medium">Fix actually applied</label>
                            <textarea id="resolution-fix" wire:model="fixApplied" rows="4" maxlength="3000"
                                class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Describe the actual change or repair and its outcome"></textarea>
                            @error('fixApplied') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                            <label for="resolution-verification" class="block text-sm font-medium">Verification actually performed</label>
                            <textarea id="resolution-verification" wire:model="verification" rows="4" maxlength="3000"
                                class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Record actual retest results or customer confirmation"></textarea>
                            @error('verification') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                            <label for="resolution-risks" class="block text-sm font-medium">Remaining risks or follow-up (optional)</label>
                            <textarea id="resolution-risks" wire:model="remainingRisks" rows="3" maxlength="2000"
                                class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>

                            <label class="flex items-start gap-2 text-sm">
                                <input type="checkbox" wire:model="verified" class="mt-1">
                                <span>I confirm the fix and verification details above reflect work actually performed,
                                    not unverified AI suggestions.</span>
                            </label>
                            @error('verified') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                                Save internal resolution handoff
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-amber-700 dark:text-amber-300">
                    Save a reviewed note through Troubleshooting Review first. No resolution handoff can
                    be created from unverified AI suggestions alone.
                </p>
            @endif
        @else
            <p class="text-sm text-gray-600 dark:text-gray-300">
                This case is resolved or closed; no new resolution handoff is allowed.
            </p>
        @endif
    @endcan

    <p class="text-xs text-gray-500 dark:text-gray-400">
        When you are ready to resolve the case, use the existing Close &amp; Resolve tab.
        This handoff does not populate or submit that form automatically.
    </p>
</section>
