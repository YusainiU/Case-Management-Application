
<div class="space-y-5">

    {{-- Notification --}}
    @if ($this->message)
        <div class="px-1">
            <x-casemanagement::page-elements.toast-alert-simple
                header="Changes Made"
                mode="information"
                message="{{ $message }}"
            />
        </div>
    @endif

    <x-casemanagement::page-elements.modal
        title="{{ $ui['title'] }}"
        description="{{ $ui['description'] }}"
        widthsize="7xl"
        cancelThisWith="closeThisModal"
        :showFooter="false"
        cancelText="Close"
    >

        {{-- Case overview --}}
        <div class="space-y-5">

            <section
                class="rounded-xl border border-slate-200
                       bg-slate-50/80 p-4 sm:p-5"
                aria-label="Case overview"
            >
                <x-casemanagement::page-elements.case-status-panel-small
                    :case="$case"
                />
            </section>

            {{-- Case description --}}
            <section
                class="rounded-xl border border-slate-200
                       bg-white p-4 sm:p-5"
                aria-labelledby="case-description-heading"
            >
                <div class="mb-3 flex items-center gap-2.5">
                    <span
                        class="flex h-8 w-8 items-center justify-center
                               rounded-lg bg-indigo-50 text-indigo-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M8 6h13" />
                            <path d="M8 12h13" />
                            <path d="M8 18h13" />
                            <path d="M3 6h.01" />
                            <path d="M3 12h.01" />
                            <path d="M3 18h.01" />
                        </svg>
                    </span>

                    <h3
                        id="case-description-heading"
                        class="text-sm font-semibold text-slate-900"
                    >
                        Case description
                    </h3>
                </div>

                <div
                    class="text-sm leading-7 text-slate-600
                           [overflow-wrap:anywhere]"
                >
                    @if (filled($case->description))
                        {{ $case->description }}
                    @else
                        <span class="italic text-slate-400">
                            No description provided.
                        </span>
                    @endif
                </div>
            </section>

            {{-- Optional AI assistant summary --}}
            {{--
            @can('ai.case_assistant.view', $case)
                <section class="rounded-xl border border-slate-200 p-5">
                    <livewire:ai-management.case-assistant :case="$case" />
                </section>
            @endcan
            --}}

        </div>

        {{-- Section separator --}}
        <div class="my-6 border-t border-slate-200"></div>

        {{-- Case navigation and content --}}
        <section class="min-w-0" aria-label="Case management sections">

            <div
                class="mb-5 overflow-x-auto rounded-xl border
                       border-slate-200 bg-slate-50 p-1"
            >
                <x-casemanagement::page-elements.tab
                    :tabHeaders="$tabHeaders"
                    selectedTab="{{ $selectedTab }}"
                    tabBgColorActive="bg-white"
                    tabBgColorInactive="bg-transparent"
                    tabBgColorInactiveHover="hover:bg-slate-100"
                    tabBgHover="hover:bg-white hover:text-slate-900"
                >
                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['sla'])
                        <livewire:manage-case-sla :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['progress'])
                        <livewire:case-progress-log :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['update'])
                        <livewire:create-case
                            :caseId="$case->id"
                            :customerId="$case->customer_id"
                            :modeUpdate="true"
                        />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['tracker'])
                        <livewire:case-field-changes :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['status'])
                        <x-casemanagement::page-elements.sla-status
                            :case="$case"
                        />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['pauses'])
                        <livewire:case-sla-pause :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['visit'])
                        <livewire:view-site-visit :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['close'])
                        <livewire:close-and-resolve-case :case="$case" />
                    @endif

                    @if ($tabHeaders[$this->selectedTab] == $tabHeaders['uploaded'])
                        @include('casemanagement::components.form-elements.upload-file')
                    @endif

                    @can('ai.case_assistant.view', $case)
                        @if ($tabHeaders[$this->selectedTab] == $tabHeaders['caseAssistant'])
                            <livewire:ai-management.case-assistant :case="$case" />
                        @endif
                    @endcan

                </x-casemanagement::page-elements.tab>
            </div>

        </section>

    </x-casemanagement::page-elements.modal>

</div>
