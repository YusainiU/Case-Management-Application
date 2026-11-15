<section class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="Resolution readiness review">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Resolution Readiness — Staff Handoff</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Prepare evidence for a possible manual resolution. AI suggestions are not proof that a fix succeeded.
            By default this saves an internal handoff only. Optionally, authorised staff can explicitly
            resolve the case using the standard Close &amp; Resolve process, which updates SLA status
            and emails the customer. Neither option closes the case.
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

                            <div class="space-y-3 rounded-md border border-amber-300 p-3 dark:border-amber-700">
                                <label class="flex items-start gap-2 text-sm font-medium">
                                    <input type="checkbox" wire:model.live="resolveCase" class="mt-1">
                                    <span>Also resolve this case using the existing Close &amp; Resolve process</span>
                                </label>
                                <p class="text-xs text-amber-800 dark:text-amber-200">
                                    Optional and unchecked by default. If selected, saving will update the case
                                    to Resolved, update its SLA task, record the normal resolution log,
                                    and <strong>send the standard resolution email to the customer</strong>.
                                    The case will not be closed. Confirm the fix actually succeeded first.
                                </p>
                                @error('resolveCase') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                @if ($resolveCase)
                                    <label for="ai-resolution-type" class="block text-sm font-medium">Resolution type (required)</label>
                                    <select id="ai-resolution-type" wire:model="resolutionType"
                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:bg-gray-900">
                                        <option value="">Select resolution</option>
                                        @foreach ($resolutionOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('resolutionType') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                                    <label for="ai-resolution-by" class="block text-sm font-medium">Resolved by (required)</label>
                                    <select id="ai-resolution-by" wire:model="resolvedBy"
                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:bg-gray-900">
                                        <option value="">Select resolver</option>
                                        @foreach ($resolverOptions as $userId => $userName)
                                            <option value="{{ $userId }}">{{ $userName }}</option>
                                        @endforeach
                                    </select>
                                    @error('resolvedBy') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                                    <label for="ai-resolved-at" class="block text-sm font-medium">Resolved at (required)</label>
                                    <input id="ai-resolved-at" type="datetime-local" wire:model="resolvedAt"
                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:bg-gray-900">
                                    @error('resolvedAt') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                @endif
                            </div>

                            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                                {{ $resolveCase ? 'Save handoff and resolve case (notify customer)' : 'Save internal resolution handoff' }}
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
        Select the optional checkbox above only when the actual resolution is verified and
        you intend to trigger the same resolution updates and customer email.
    </p>
</section>
