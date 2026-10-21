@php
    use Simplydigital\CaseManagement\Services\UI;

    $action = 'createLog';
    $label = 'Add Progress Log';
    $enableCancel = false;

    if ($respondToComment) {
        $action = "createRespond({$respondToLog->id})";
        $label = 'Add Response';
        $enableCancel = true;
    }

    if ($logToEdit) {
        $action = 'editThisLog';
        $label = 'Save Changes';
        $enableCancel = true;
    }

    $caseOpen = !$case->closed_datetime;
@endphp

<div
    x-data
    x-on:modal-scroll-top.window="$refs.modalBody.scrollTo({ top: 0, behavior: 'smooth' })"
    class="text-slate-800"
>
    <div
        x-ref="modalBody"
        class="max-h-[80vh] overflow-y-auto px-1 pb-2 sm:px-2"
    >

        {{-- Context: responding to a log --}}
        @if ($respondToComment)
            <div class="mb-4 overflow-hidden rounded-xl border border-indigo-100 bg-indigo-50/60">
                <div class="flex items-start gap-3 p-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-indigo-600 ring-1 ring-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 10h8m-8 4h5m-9 5l2.5-3H17a3 3 0 003-3V6a3 3 0 00-3-3H7a3 3 0 00-3 3v10a3 3 0 001 2.3V19z" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-indigo-950">
                            Responding to progress log #{{ $respondToLog->number }}
                        </p>

                        <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-slate-700">
                            {{ $respondToLog->description }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Context: editing a log --}}
        @if ($logToEdit)
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 shrink-0 text-amber-700"
                    fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.7"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                </svg>

                <div>
                    <p class="text-sm font-semibold text-amber-900">
                        Editing progress log #{{ $logToEdit->number }}
                    </p>
                    <p class="text-xs text-amber-800">
                        Your changes will update this existing entry.
                    </p>
                </div>
            </div>
        @endif

        {{-- Create / edit / respond form --}}
        @if ($caseOpen)
            @can('update', $case)
                <section class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                        <h3 class="text-sm font-semibold text-slate-900">
                            {{ $label }}
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Record an update to keep the case history accurate.
                        </p>
                    </div>

                    <form wire:submit="{{ $action }}" class="space-y-4 p-4 sm:p-5">

                        <div>
                            <x-casemanagement::form-elements.textarea
                                model="logText"
                                hint="Describe the progress, actions taken or next steps."
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                                <div class="pt-0.5">
                                    <x-casemanagement::form-elements.checkbox
                                        label="Urgent Attention"
                                        model="urgentAttention"
                                        name="Urgent Attention"
                                    />
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                                <div class="pt-0.5">
                                    <x-casemanagement::form-elements.checkbox
                                        label="Email Customer Contact"
                                        model="notifyCustomer"
                                        name="Email Customer Contact"
                                    />
                                </div>
                            </label>

                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4">

                            @if ($enableCancel)
                                <x-casemanagement::form-elements.button-secondary
                                    action="cancelRespond"
                                    label="Cancel"
                                />
                            @endif

                            <x-casemanagement::form-elements.button-primary
                                action="{{ $action }}"
                                label="{{ $label }}"
                            />

                        </div>
                    </form>
                </section>
            @endcan
        @endif

        {{-- Timeline heading --}}
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold tracking-tight text-slate-900">
                    Case Activity
                </h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    A chronological record of case updates and actions.
                </p>
            </div>

            <span class="shrink-0 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">
                {{ $taskLogs->count() }} {{ $taskLogs->count() === 1 ? 'entry' : 'entries' }}
            </span>
        </div>

        {{-- Activity timeline --}}
        @forelse ($taskLogs as $log)

            @php
                $attention = $log->require_attention
                    ? UI::ALERT_WARNING
                    : null;
            @endphp

            @if ($logToDelete == $log->id)
                <div class="mb-3">
                    <x-casemanagement::page-elements.confirm-delete
                        recordToDelete="{{ $log->number }}"
                        action="deleteThisLog"
                        deleteText="Delete this progress log"
                    />
                </div>
            @endif

            <article
                wire:key="progress-log-{{ $log->id }}"
                class="relative pb-5 last:pb-0"
            >
                {{-- Timeline connector --}}
                @if (!$loop->last)
                    <span
                        class="absolute bottom-0 left-4 top-10 w-px bg-slate-200"
                        aria-hidden="true"
                    ></span>
                @endif

                <div class="relative flex items-start gap-3 sm:gap-4">

                    {{-- Timeline marker --}}
                    <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-indigo-600 ring-4 ring-white">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                        </svg>
                    </div>

                    {{-- Entry card --}}
                    <div class="min-w-0 flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        {{-- Entry header --}}
                        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-slate-900">
                                        {{ $log->type }}
                                    </h4>

                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                        #{{ $log->number }}
                                    </span>

                                    @if ($log->require_attention)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5"
                                                fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v3.75m9.303 3.376L13.303 3.82a1.5 1.5 0 00-2.606 0L2.697 16.126A1.5 1.5 0 003.999 18.4h16.002a1.5 1.5 0 001.302-2.274zM12 16.5h.008v.008H12V16.5z" />
                                            </svg>
                                            Urgent
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $log->user?->name ?? 'System' }}
                                    <span class="mx-1 text-slate-300">·</span>
                                    {{ $log->created_at?->format('d M Y, H:i') }}
                                </p>
                            </div>

                            {{-- Attention action --}}
                            @if ($attention && $caseOpen)
                                @can('update', $case)
                                    <div class="shrink-0">
                                        <x-casemanagement::form-elements.button-hover-change-action
                                            size="small"
                                            hover_on_label="Remove Urgent Flag"
                                            hover_off_label="Urgent Attention"
                                            hover_off="{{ $attention }}"
                                            action_hover_on="removeRequireAttention({{ $log->id }})"
                                        />
                                    </div>
                                @endcan
                            @endif
                        </div>

                        {{-- Entry description --}}
                        <div class="px-4 py-4">

                            @if ($log->description)
                                <div class="whitespace-pre-line break-words text-sm leading-6 text-slate-700">
                                    {{ $log->description }}
                                </div>
                            @else
                                <p class="text-sm italic text-slate-400">
                                    No description provided.
                                </p>
                            @endif

                            {{-- Parent internal comment --}}
                            @if ($log->response_to_internal_comment && $log->parentInternalComment)
                                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/70 p-3">

                                    <div class="mb-1 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 shrink-0 text-amber-700"
                                            fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.7"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 10h8m-8 4h5m-9 5l2.5-3H17a3 3 0 003-3V6a3 3 0 00-3-3H7a3 3 0 00-3 3v10a3 3 0 001 2.3V19z" />
                                        </svg>

                                        <p class="text-xs font-semibold text-amber-900">
                                            Response to internal log #{{ $log->parentInternalComment->number }}
                                        </p>
                                    </div>

                                    <p class="whitespace-pre-line break-words text-sm leading-5 text-amber-950/80">
                                        {{ $log->parentInternalComment->description }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Entry footer --}}
                        <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/70 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                            <div class="text-xs text-slate-500">
                                <span class="font-medium text-slate-600">Source:</span>
                                {{ ucwords(str_replace('_', ' ', $log->source ?? 'Unknown')) }}
                            </div>

                            @can('update', $case)
                                @if ($caseOpen)
                                    <div class="flex flex-wrap items-center gap-2">

                                        <x-casemanagement::form-elements.button-secondary
                                            action="respondLog({{ $log->id }})"
                                            label="Respond"
                                            size="small"
                                        />

                                        <x-casemanagement::form-elements.button-secondary
                                            action="editLog({{ $log->id }})"
                                            label="Edit"
                                            size="small"
                                        />

                                        <x-casemanagement::form-elements.button-danger
                                            action="deleteLog({{ $log->id }})"
                                            label="Delete"
                                            size="small"
                                        />

                                    </div>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </article>

        @empty
            {{-- Empty state --}}
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center">

                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-400 ring-1 ring-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                </div>

                <h4 class="mt-3 text-sm font-semibold text-slate-900">
                    No progress logs yet
                </h4>

                <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                    Case updates, responses and recorded actions will appear here.
                </p>
            </div>
        @endforelse

        {{-- Information message --}}
        @if ($this->message)
            <div class="mt-4">
                <x-casemanagement::page-elements.toast-alert-simple
                    mode="information"
                    message="{{ $this->message }}"
                    header="Information"
                />
            </div>
        @endif

    </div>
</div>
