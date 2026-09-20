@php
    use Simplydigital\CaseManagement\Services\UI;
    $action = 'createLog';
    $label = 'Create';
    $enableCancel = false;
    if ($respondToComment) {
        $action = "createRespond($respondToLog->id)";
        $label = 'Create Respond';
        $enableCancel = true;
    }
    if ($logToEdit) {
        $action = 'editThisLog';
        $label = 'Edit Log';
        $enableCancel = true;
    }

    $caseOpen = !$taskLogs->first()->ticket->closed_datetime;

@endphp

<div x-data x-on:modal-scroll-top.window="$refs.modalBody.scrollTo({ top: 0, behavior: 'smooth' })">

    <div class="max-h-[80vh] overflow-y-auto pr-4" x-ref="modalBody">

        @if ($respondToComment)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm mb-3">
                <div>
                    <p class="font-medium text-gray-900">
                        Responding to ..
                    </p>
                    <div class="mt-3 text-sm text-gray-700">
                        {{ $respondToLog->description }}
                    </div>
                </div>
            </div>
        @endif

        @if ($logToEdit)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm mb-3">
                <div>
                    <p class="font-medium text-gray-900">
                        Editing Log # {{ $logToEdit->number }} ..
                    </p>
                </div>
            </div>
        @endif

        @if ($caseOpen)
            @can('update', $case)
                <div class="flex flex-col gap-3">
                    <x-casemanagement::form-elements.textarea model="logText" hint="Progress Log" />
                    <div class="flex flex-row gap-3">
                        <x-casemanagement::form-elements.checkbox label="Urgent Attention" model="urgentAttention"
                            name="Urgent Attention" />
                        <x-casemanagement::form-elements.checkbox label="Email Customer Contact" model="notifyCustomer"
                            name="Email Customer Contact" />
                    </div>
                    <div class="flex flex-row gap-3">
                        <x-casemanagement::form-elements.button-primary action="{{ $action }}"
                            label="{{ $label }}" />
                        @if ($enableCancel)
                            <x-casemanagement::form-elements.button-secondary action="cancelRespond" label="Cancel" />
                        @endif
                    </div>
                </div>
                <x-casemanagement::page-elements.seperator />
            @endcan
        @endif


        <ul role="list" class="-mb-8">

            @foreach ($taskLogs as $log)
                @php
                    $attention = null;
                    if ($log->require_attention) {
                        $attention = UI::ALERT_WARNING;
                    }

                @endphp

                @if ($logToDelete == $log->id)
                    <x-casemanagement::page-elements.confirm-delete recordToDelete="{{ $log->number }}"
                        action="deleteThisLog" deleteText="Delete this Log" />
                @endif

                <li>
                    <div class="relative pb-8">

                        @if (!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                aria-hidden="true"></span>
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

                                            <p class="text-sm text-gray-500">
                                                Log Number # {{ $log->number }}
                                            </p>

                                        </div>

                                        @if ($attention && $caseOpen)
                                            {{-- <div class="{{ $attention }} px-2 py-2 rounded-md text-xs">Require Attention</div> --}}
                                            <x-casemanagement::form-elements.button-hover-change-action size="small"
                                                hover_on_label="Remove Require Attention"
                                                hover_off_label="Require Attention" hover_off="{{ $attention }}"
                                                action_hover_on="removeRequireAttention({{ $log->id }})" />
                                        @endif

                                        <time class="text-sm text-gray-500">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </time>
                                    </div>

                                    @if ($log->description)
                                        <div class="mt-3 text-sm text-gray-700">
                                            {{ $log->description }}
                                            @if ($log->response_to_internal_comment)
                                                <p class="flex flex-col gap-1 mt-2 p-2 rounded-2xl bg-amber-100">
                                                    <span class="font-extrabold">This is a response to log #
                                                        {{ $log->parentInternalComment->number }}...</span>
                                                    <span
                                                        class="italic">{{ $log->parentInternalComment->description }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex flex-col gap-2 mt-4 rounded bg-gray-50 p-3 text-sm">
                                        @can('update', $case)
                                            @if ($caseOpen)
                                                <div class="flex flex-row gap-3">
                                                    <x-casemanagement::form-elements.button-secondary
                                                        action="respondLog({{ $log->id }})" label="Respond"
                                                        size="small" />
                                                    <x-casemanagement::form-elements.button-danger
                                                        action="deleteLog({{ $log->id }})" label="Delete"
                                                        size="small" />
                                                    <x-casemanagement::form-elements.button-secondary
                                                        action="editLog({{ $log->id }})" label="Edit"
                                                        size="small" />
                                                </div>
                                            @endif
                                        @endcan
                                        <div>
                                            <span class="font-medium text-red-600">Source:</span>
                                            <span class="text-gray-900">{{ ucwords($log->source) }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </li>
            @endforeach

        </ul>
        @if ($this->message)
            <x-casemanagement::page-elements.toast-alert-simple mode="information" message="{{ $message }}"
                header="Information" />
        @endif
    </div>
</div>
x
