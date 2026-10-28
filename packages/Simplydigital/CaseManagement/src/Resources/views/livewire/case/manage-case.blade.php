
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
        width="w-full max-w-[88rem]"
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
        </div>

        {{-- Section separator --}}
        <div class="my-6 border-t border-slate-200"></div>

        {{-- Case navigation and content --}}
        <section
            class="grid min-w-0 grid-cols-1 gap-4
                   sm:grid-cols-[14rem_minmax(0,1fr)]"
            aria-label="Case management sections"
        >
            <nav
                class="h-fit rounded-xl border border-slate-200
                       bg-slate-50 p-2"
                aria-label="Case management navigation"
            >
                <ul class="space-y-1">
                    @foreach ($tabHeaders as $header => $headerName)
                        <li wire:key="case-section-{{ $header }}">
                            <button
                                type="button"
                                wire:click="selectTab('{{ $header }}')"
                                aria-current="{{ $header === $selectedTab ? 'page' : 'false' }}"
                                class="{{ $header === $selectedTab
                                    ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200'
                                    : 'text-slate-600 hover:bg-white hover:text-slate-900' }}
                                    flex w-full items-center rounded-lg px-3 py-2.5
                                    cursor-pointer text-left text-sm font-medium transition
                                    focus:outline-none focus-visible:ring-2
                                    focus-visible:ring-indigo-500"
                            >
                                {{ $headerName }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                @if ($this->selectedTab === 'description')
                    <section aria-labelledby="case-description-heading">
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
                @endif

                @if ($tabHeaders[$this->selectedTab] == $tabHeaders['sla'])
                    <livewire:manage-case-sla :case="$case" />
                @endif

                @if ($tabHeaders[$this->selectedTab] == $tabHeaders['progress'])
                    <livewire:case-progress-log :case="$case" />
                @endif

                @if ($tabHeaders[$this->selectedTab] == $tabHeaders['stockRequests'])
                    <livewire:stock-request-case :case="$case" />
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

                @if ($this->selectedTab === 'expenses' && isset($tabHeaders['expenses']))
                    <livewire:expense-claims :case="$case" />
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

            </div>
        </section>

    </x-casemanagement::page-elements.modal>

</div>
