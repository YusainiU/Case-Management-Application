@php
    use Simplydigital\CaseManagement\Services\UI;
@endphp

<div>
    <div class="flow-root">
    
        <div class="flex flex-col gap-3">
            <x-casemanagement::form-elements.textarea model="logText" hint="Create New Progress Log" />
            <x-casemanagement::form-elements.checkbox label="Urgent Attention" model="urgentAttention" name="Urgent Attention"/>
            <x-casemanagement::form-elements.button-primary action="createLog" label="Create" />
        </div>
        <x-casemanagement::page-elements.seperator />                
        <ul role="list" class="-mb-8">

            @foreach ($taskLogs as $log)
                @php
                    $attention = null;
                    if($log->require_attention){
                        $attention = UI::ALERT_WARNING;
                    }

                @endphp
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

                                            <p class="text-sm text-gray-500">
                                                Log Number # {{ $log->number }}
                                            </p>

                                        </div>

                                        @if($attention)
                                            {{-- <div class="{{ $attention }} px-2 py-2 rounded-md text-xs">Require Attention</div> --}}
                                            <x-casemanagement::form-elements.button-hover-change-action 
                                                size="small"
                                                hover_on_label="Remove Require Attention"
                                                hover_off_label="Require Attention"
                                                hover_off="{{ $attention }}"
                                                action_hover_on="removeRequireAttention({{ $log->id }})"
                                            />
                                        @endif

                                        <time class="text-sm text-gray-500">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </time>
                                    </div>

                                    @if ($log->description)
                                        <div class="mt-3 text-sm text-gray-700">
                                            {{ $log->description }}
                                        </div>
                                    @endif

                                    <div class="mt-4 rounded bg-gray-50 p-3 text-sm">

                                        <div>
                                            <x-casemanagement::form-elements.button-secondary action="respondLog" label="Respond" size="small" />
                                            
                                        </div>

                                        <div>
                                            <span class="font-medium text-red-600">Source:</span>
                                            <span class="text-gray-900">{{ ucwords($log->source) }}</span>
                                        </div>

                                        @if ($log->sla_task_id)
                                            <div>
                                                <span class="font-medium text-red-600">SlA Task:</span>
                                                <span
                                                    class="text-gray-900">{{ $log->slaTask?->short_description }}</span>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </li>
            @endforeach

        </ul>
        @if($this->message)
            <x-casemanagement::page-elements.toast-alert-simple mode="information" message="{{ $message }}" header="Information"/>
        @endif
    </div>
</div>
x