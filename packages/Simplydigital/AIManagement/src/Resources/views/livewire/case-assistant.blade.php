<div wire:poll.8s="refreshAnalysis" class="ai-assistant-shell space-y-5 rounded-xl p-3 sm:p-5" aria-label="AI Case Assistant">
    @once
        <style>
            .ai-assistant-shell {
                --ai-bg: #f8fafc;
                --ai-panel: #ffffff;
                --ai-border: #cbd5e1;
                --ai-ink: #0f172a;
                --ai-muted: #475569;
                --ai-accent: #4338ca;
                --ai-soft: #eef2ff;
                color: var(--ai-ink);
                background: var(--ai-bg);
                color-scheme: light;
            }
            .ai-assistant-shell .ai-card {
                background: var(--ai-panel);
                border: 1px solid var(--ai-border);
                border-radius: 0.75rem;
            }
            .ai-assistant-shell .ai-muted { color: var(--ai-muted); }
            .ai-assistant-shell .ai-timeline {
                border-inline-start: 2px solid var(--ai-border);
                margin-inline-start: 0.65rem;
            }
            .ai-assistant-shell .ai-timeline-item {
                position: relative;
                padding-inline-start: 1.5rem;
            }
            .ai-assistant-shell .ai-timeline-item::before {
                content: '';
                position: absolute;
                top: 0.6rem;
                inset-inline-start: -0.45rem;
                width: 0.8rem;
                height: 0.8rem;
                border: 2px solid var(--ai-panel);
                border-radius: 50%;
                background: var(--ai-accent);
                box-shadow: 0 0 0 1px var(--ai-border);
            }
             .ai-assistant-shell .ai-workflow-toggle {
                cursor: pointer;
                padding: 1rem;
                font-weight: 650;
                color: var(--ai-ink);
            }
            .ai-assistant-shell .ai-workflow-toggle:focus-visible,
            .ai-assistant-shell button:focus-visible,
            .ai-assistant-shell a:focus-visible {
                outline: 3px solid var(--ai-accent);
                outline-offset: 3px;
            }
            .ai-assistant-shell .ai-workflow-toggle[aria-expanded="true"] { border-bottom: 1px solid var(--ai-border); }
            .ai-assistant-shell .ai-workflow-content { padding: 0.25rem 1rem 1.25rem; }
            .ai-assistant-shell .ai-workflow-content > section { margin-top: 0; border-top: 0; padding-top: 0.5rem; }
            @media (prefers-color-scheme: dark) {
                .ai-assistant-shell:not(.light) {
                    --ai-bg: #0f172a;
                    --ai-panel: #1e293b;
                    --ai-border: #475569;
                    --ai-ink: #f1f5f9;
                    --ai-muted: #cbd5e1;
                    --ai-accent: #a5b4fc;
                    --ai-soft: #312e81;
                    color-scheme: dark;
                }
            }
            .dark .ai-assistant-shell {
                --ai-bg: #0f172a;
                --ai-panel: #1e293b;
                --ai-border: #475569;
                --ai-ink: #f1f5f9;
                --ai-muted: #cbd5e1;
                --ai-accent: #a5b4fc;
                --ai-soft: #312e81;
                color-scheme: dark;
            }
            .light .ai-assistant-shell {
                --ai-bg: #f8fafc;
                --ai-panel: #ffffff;
                --ai-border: #cbd5e1;
                --ai-ink: #0f172a;
                --ai-muted: #475569;
                --ai-accent: #4338ca;
                --ai-soft: #eef2ff;
                color-scheme: light;
            }
            /* Inputs and nested workflow content also follow system dark mode
               when the application does not explicitly set a theme class. */
            @media (prefers-color-scheme: dark) {
                .ai-assistant-shell:not(.light) input:not([type="checkbox"]),
                .ai-assistant-shell:not(.light) select,
                .ai-assistant-shell:not(.light) textarea {
                    background: var(--ai-panel);
                    color: var(--ai-ink);
                    border-color: var(--ai-border);
                }
            }
            .dark .ai-assistant-shell input:not([type="checkbox"]),
            .dark .ai-assistant-shell select,
            .dark .ai-assistant-shell textarea {
                background: var(--ai-panel);
                color: var(--ai-ink);
                border-color: var(--ai-border);
            }
            .light .ai-assistant-shell input:not([type="checkbox"]),
            .light .ai-assistant-shell select,
            .light .ai-assistant-shell textarea {
                background: var(--ai-panel);
                color: var(--ai-ink);
                border-color: var(--ai-border);
            }
            /* Existing Livewire children use Tailwind grey utility classes.
               Provide readable contrast even when only the OS dark preference
               is active and the app has not added a .dark class. */
            @media (prefers-color-scheme: dark) {
                .ai-assistant-shell .ai-workflow-content :is(.text-gray-500, .text-gray-600, .text-gray-700, .text-gray-800) {
                    color: var(--ai-muted);
                }
                .ai-assistant-shell .ai-workflow-content :is(.bg-gray-100, .bg-white) {
                    background-color: var(--ai-panel);
                    color: var(--ai-ink);
                }
            }
            .dark .ai-assistant-shell .ai-workflow-content :is(.text-gray-500, .text-gray-600, .text-gray-700, .text-gray-800) {
                color: var(--ai-muted);
            }
            .dark .ai-assistant-shell .ai-workflow-content :is(.bg-gray-100, .bg-white) {
                background-color: var(--ai-panel);
                color: var(--ai-ink);
            }
            .light .ai-assistant-shell .ai-workflow-content :is(.text-gray-500, .text-gray-600, .text-gray-700, .text-gray-800) {
                color: var(--ai-muted);
            }
            .light .ai-assistant-shell .ai-workflow-content :is(.bg-gray-100, .bg-white) {
                background-color: var(--ai-panel);
                color: var(--ai-ink);
            }
            @media (prefers-reduced-motion: reduce) {
                .ai-assistant-shell *, .ai-assistant-shell *::before { scroll-behavior: auto !important; }
            }
        </style>
    @endonce

    <header class="ai-card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold">AI Case Assistant</h2>
            <p class="ai-muted mt-1 text-sm">
                Case {{ $case->number ?? '#'.$case->getKey() }} · Activity newest first · Human approval for case changes
            </p>
        </div>
        @can('case-assistant.analyze', $case)
            @unless ($analyzing)
                <button type="button" wire:click="analyze" wire:loading.attr="disabled" wire:target="analyze"
                    class="w-full shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="analyze">{{ $result ? 'Re-analyze Case' : 'Analyze Case' }}</span>
                    <span wire:loading wire:target="analyze">Starting analysis…</span>
                </button>
            @endunless
        @endcan
    </header>

    @if ($analysis && in_array($analysis->status, ['pending', 'processing', 'failed'], true))
        <div class="ai-card p-4 text-sm" role="status">
            <strong>Case analysis: {{ ucfirst($analysis->status) }}</strong>
            <span class="ai-muted"> · {{ $analysis->updated_at?->timezone($timelineTimezone)->format('d M Y, H:i:s') }} {{ $timelineTimezone }}</span>
            @if ($analysis->status === 'failed')
                <p class="mt-2 text-red-700 dark:text-red-300">The latest analysis failed. You may try analysing the case again.</p>
            @else
                <p class="ai-muted mt-1">The timeline and analysis status refresh automatically.</p>
            @endif
        </div>
    @endif

    <nav class="ai-card flex flex-wrap items-center justify-between gap-3 p-3 sm:p-4" aria-label="Assistant display controls">
        <div>
            <p class="text-sm font-semibold">Display mode</p>
            <p class="ai-muted text-xs">Focus on the next action, or show the full case history.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="setFocusView(true)"
                aria-pressed="{{ ! $showOverview && ! $showTimeline ? 'true' : 'false' }}"
                class="rounded-md border px-3 py-2 text-xs font-semibold"
                style="border-color:var(--ai-border);{{ ! $showOverview && ! $showTimeline ? 'background:var(--ai-soft);color:var(--ai-accent);' : 'color:var(--ai-ink);' }}">
                Focus view
            </button>
            <button type="button" wire:click="setFocusView(false)"
                aria-pressed="{{ $showOverview && $showTimeline ? 'true' : 'false' }}"
                class="rounded-md border px-3 py-2 text-xs font-semibold"
                style="border-color:var(--ai-border);{{ $showOverview && $showTimeline ? 'background:var(--ai-soft);color:var(--ai-accent);' : 'color:var(--ai-ink);' }}">
                Full view
            </button>
        </div>
    </nav>

    <section class="ai-card p-4 sm:p-5" aria-label="Suggested next step" id="ai-suggested-next-step">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="ai-muted text-xs font-semibold uppercase tracking-wide">Suggested next step · Advisory only</p>
                <h3 class="mt-1 text-base font-bold">{{ $suggestedNextStep['title'] }}</h3>
                <p class="ai-muted mt-2 max-w-3xl break-words text-sm">{{ $suggestedNextStep['detail'] }}</p>
            </div>
            <a href="#ai-workflow-{{ $suggestedNextStep['workflow'] }}"
                wire:click="openWorkflow('{{ $suggestedNextStep['workflow'] }}')"
                class="shrink-0 self-start rounded-lg border px-4 py-2 text-sm font-semibold underline-offset-2 hover:underline"
                style="border-color:var(--ai-border);color:var(--ai-accent)">
                Open suggested workflow
            </a>
        </div>
        <p class="ai-muted mt-3 text-xs">
            Guidance only. No case changes, approvals, emails or AI requests occur when opening a workflow.
            Staff can choose any permitted workflow.
        </p>
    </section>

    <section class="ai-card p-4 sm:p-5" aria-label="Case workflow overview" id="ai-workflow-overview">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold">Case workflow overview</h3>
                <p class="ai-muted mt-1 text-sm">Based on the current case and recorded reviews. A stage is not proof of an AI action unless stated.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="ai-muted rounded-md border px-2 py-1 text-xs" style="border-color:var(--ai-border)">
                    Updated automatically
                </span>
                <button type="button" wire:click="toggleOverview"
                    aria-expanded="{{ $showOverview ? 'true' : 'false' }}"
                    aria-controls="ai-workflow-overview-content"
                    class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                    style="border-color:var(--ai-border);color:var(--ai-accent)">
                    {{ $showOverview ? 'Hide overview' : 'Show overview' }}
                </button>
            </div>
        </div>
        @if ($showOverview)
        <div id="ai-workflow-overview-content">
        <ol class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($workflowOverview as $stage)
                <li class="min-w-0 rounded-lg border p-3" style="border-color:var(--ai-border)"
                    wire:key="overview-{{ $stage['workflow'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h4 class="text-sm font-semibold">{{ $loop->iteration }}. {{ $stage['title'] }}</h4>
                        <span class="rounded-full border px-2 py-0.5 text-xs font-medium"
                            style="border-color:var(--ai-border);{{ match ($stage['status']) {
                                'complete' => 'color:#15803d;',
                                'attention' => 'color:#b91c1c;',
                                'pending', 'ready' => 'color:var(--ai-accent);',
                                default => 'color:var(--ai-muted);'
                            } }}">
                            {{ match ($stage['status']) {
                                'complete' => 'Recorded',
                                'attention' => 'Needs review',
                                'pending' => 'In progress',
                                'ready' => 'Ready for review',
                                default => 'Not recorded',
                            } }}
                        </span>
                    </div>
                    <p class="ai-muted mt-2 break-words text-xs">{{ $stage['detail'] }}</p>
                    @if ($stage['at'])
                        <time class="ai-muted mt-2 block text-xs tabular-nums"
                            datetime="{{ $stage['at']->timezone($timelineTimezone)->toIso8601String() }}">
                            {{ $stage['at']->timezone($timelineTimezone)->format('d M Y · H:i') }} {{ $timelineTimezone }}
                        </time>
                    @endif
                    <a href="#ai-workflow-{{ $stage['workflow'] }}"
                        wire:click="openWorkflow('{{ $stage['workflow'] }}')"
                        class="mt-3 inline-block text-xs font-semibold underline underline-offset-2"
                        style="color:var(--ai-accent)">
                        Open stage
                    </a>
                </li>
            @endforeach
        </ol>
        </div>
        @endif
    </section>

    <section class="ai-card p-4 sm:p-5" aria-label="AI Assistant items needing attention" id="ai-needs-attention">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold">Needs attention</h3>
                <p class="ai-muted text-sm">Current outstanding approvals and problems, not historical alerts.</p>
            </div>
            <span class="rounded-full border px-3 py-1 text-xs font-semibold tabular-nums"
                style="border-color:var(--ai-border);color:var(--ai-ink)">
                {{ count($attentionItems) }} {{ count($attentionItems) === 1 ? 'item' : 'items' }}
            </span>
        </div>
        @if (empty($attentionItems))
            <p class="ai-muted mt-3 text-sm">No outstanding AI approvals or recorded failures requiring review.</p>
        @else
            <ul class="mt-4 space-y-2" aria-label="Current attention items">
                @foreach ($attentionItems as $item)
                    <li wire:key="attention-{{ $item['key'] }}" class="rounded-lg border p-3"
                        style="border-color:var(--ai-border)">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="break-words text-sm font-semibold {{ $item['severity'] === 'error' ? 'text-red-700 dark:text-red-300' : 'text-amber-800 dark:text-amber-300' }}">
                                    {{ $item['title'] }}
                                </p>
                                <p class="ai-muted mt-1 break-words text-xs">{{ $item['detail'] }}</p>
                                @if ($item['at'])
                                    <time class="ai-muted mt-1 block text-xs tabular-nums"
                                        datetime="{{ $item['at']->timezone($timelineTimezone)->toIso8601String() }}">
                                        {{ $item['at']->timezone($timelineTimezone)->format('d M Y · H:i:s') }} {{ $timelineTimezone }}
                                    </time>
                                @endif
                            </div>
                            <a href="#ai-workflow-{{ $item['workflow'] }}"
                                wire:click="openWorkflow('{{ $item['workflow'] }}')"
                                class="shrink-0 text-xs font-semibold underline underline-offset-2"
                                style="color:var(--ai-accent)">
                                Review workflow
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if (count($attentionItems) === 12)
                <p class="ai-muted mt-2 text-xs">Showing up to 12 recent items. Open the relevant workflow for full history.</p>
            @endif
        @endif
    </section>

    <section class="ai-card p-4 sm:p-5" aria-label="AI activity timeline" id="ai-activity-timeline">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold">Activity timeline</h3>
                <p class="ai-muted text-sm">Recent AI actions, decisions, troubleshooting and resolution events.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="ai-muted rounded-md border px-2 py-1 text-xs" style="border-color:var(--ai-border)">
                    Times shown in {{ $timelineTimezone }}
                </span>
                <button type="button" wire:click="toggleTimeline"
                    aria-expanded="{{ $showTimeline ? 'true' : 'false' }}"
                    aria-controls="ai-timeline-content"
                    class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                    style="border-color:var(--ai-border);color:var(--ai-accent)">
                    {{ $showTimeline ? 'Hide timeline' : 'Show timeline' }}
                </button>
            </div>
        </div>

        @if ($showTimeline)
        <div id="ai-timeline-content">
        <nav class="mt-4 flex flex-wrap gap-2" aria-label="Filter AI activity">
            @foreach (['all' => 'All activity', 'analysis' => 'Analyses', 'approvals' => 'Approvals', 'reviews' => 'Reviewed notes', 'notifications' => 'Customer emails'] as $filterKey => $filterLabel)
                <button type="button" wire:click="setTimelineFilter('{{ $filterKey }}')"
                    aria-pressed="{{ $timelineFilter === $filterKey ? 'true' : 'false' }}"
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold"
                    style="border-color:var(--ai-border);{{ $timelineFilter === $filterKey ? 'background:var(--ai-soft);color:var(--ai-accent);' : 'color:var(--ai-ink);' }}">
                    {{ $filterLabel }}
                </button>
            @endforeach
        </nav>

        @if ($timelineEntries->isEmpty())
            <div class="ai-muted mt-5 rounded-lg border border-dashed p-5 text-sm" style="border-color:var(--ai-border)">
                @if ($timelineFilter === 'all')
                    No AI activity has been recorded in Case Progress yet. New events will appear here automatically.
                    Older events may require the optional AI progress backfill.
                @else
                    No activity matches this filter. Select All activity to see the full AI timeline.
                @endif
            </div>
        @else
            <ol class="ai-timeline mt-5 space-y-4" aria-label="Newest AI actions first">
                @php $lastTimelineDay = null; @endphp
                @foreach ($timelineEntries as $entry)
                    @php
                        $source = (string) $entry->source;
                        $key = (string) ($entry->short_description ?? '');
                        $event = $source === 'ai_agent' ? str($key)->afterLast(':') : null;
                        $actionName = $source === 'ai_agent'
                            ? (str((string) $entry->description)->before(' — ') ?: 'AI Agent')
                            : ($source === 'ai_assisted' ? 'Troubleshooting review' : 'Resolution handoff');
                        $heading = $source === 'ai_agent'
                            ? (str($key)->startsWith('AI:resolution-notification:')
                                ? 'Customer notification · '.ucfirst($event)
                                : (str($key)->startsWith('AI:analysis:')
                                    ? 'Case Assistant · '.ucfirst($event)
                                    : $actionName.' · '.ucfirst($event)))
                            : $actionName.' saved';
                        $statusColor = match ($event) {
                            'completed', 'sent', 'approved' => 'text-emerald-700 dark:text-emerald-300',
                            'failed', 'rejected', 'stale' => 'text-red-700 dark:text-red-300',
                            'pending', 'queued', 'requested' => 'text-amber-700 dark:text-amber-300',
                            default => 'ai-muted',
                        };
                        $workflow = match (true) {
                            $source === 'ai_assisted' => 'troubleshoot',
                            $source === 'ai_resolution_review' => 'resolution',
                            str($key)->startsWith('AI:resolution-notification:') => 'resolution',
                            str($key)->startsWith('AI:analysis:') => 'analysis',
                            str($key)->startsWith('AI:proposal:') && str((string) $entry->description)->startsWith('Category change') => 'category',
                            str($key)->startsWith('AI:proposal:') && str((string) $entry->description)->startsWith('Engineer assignment') => 'engineer',
                            str($key)->startsWith('AI:proposal:') && str((string) $entry->description)->startsWith('Start Work') => 'start-work',
                            default => null,
                        };
                        $date = $entry->created_at?->timezone($timelineTimezone);
                        $timelineDay = $date?->format('Y-m-d');
                        $newTimelineDay = $timelineDay !== $lastTimelineDay;
                        $lastTimelineDay = $timelineDay;
                    @endphp
                    @if ($newTimelineDay && $date)
                        <li class="ai-timeline-item" wire:key="ai-timeline-date-{{ $timelineDay }}">
                            <div class="flex flex-wrap items-center gap-2 py-1">
                                <time datetime="{{ $timelineDay }}"
                                    class="rounded-md border px-3 py-1.5 text-xs font-bold tracking-wide"
                                    style="border-color:var(--ai-border);background:var(--ai-soft);color:var(--ai-accent)">
                                    {{ $date->format('l, d F Y') }}
                                </time>
                            </div>
                        </li>
                    @endif
                    <li class="ai-timeline-item" wire:key="ai-timeline-entry-{{ $entry->id }}">
                        <article class="ai-card min-w-0 p-3 sm:p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
                                <h4 class="min-w-0 break-words text-sm font-semibold {{ $statusColor }}">{{ $heading }}</h4>
                                @if ($date)
                                    <time class="ai-muted shrink-0 text-xs font-medium tabular-nums" datetime="{{ $date->toIso8601String() }}"
                                        title="{{ $date->format('d M Y, H:i:s T') }}">
                                        {{ $date->format('d M Y') }} · {{ $date->format('H:i:s') }}
                                    </time>
                                @endif
                            </div>
                            <div class="ai-muted mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                <span>{{ $entry->user?->name ?? 'System / AI Agent' }}</span>
                                <span>{{ $source === 'ai_agent' ? 'System audit' : 'Staff-reviewed note' }}</span>
                            </div>
                            @if ($workflow)
                                <a href="#ai-workflow-{{ $workflow }}" wire:click="openWorkflow('{{ $workflow }}')"
                                    class="mt-2 inline-block text-xs font-semibold underline underline-offset-2"
                                    style="color:var(--ai-accent)">
                                    Open related workflow
                                </a>
                            @endif
                            @if ($entry->description)
                                <p class="mt-2 whitespace-pre-line break-words text-sm">{{ str($entry->description)->limit(260) }}</p>
                                @if (mb_strlen($entry->description) > 260)
                                    <details class="mt-2 text-sm">
                                        <summary class="cursor-pointer font-medium underline underline-offset-2" style="color:var(--ai-accent)">Read full activity</summary>
                                        <p class="mt-2 whitespace-pre-line break-words">{{ $entry->description }}</p>
                                    </details>
                                @endif
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
            @if ($timelineHasMore)
                <div class="mt-5 text-center">
                    <button type="button" wire:click="loadMoreTimeline" wire:loading.attr="disabled" wire:target="loadMoreTimeline"
                        class="rounded-md border px-4 py-2 text-sm font-semibold" style="border-color:var(--ai-border);color:var(--ai-accent)">
                        Show older activity
                    </button>
                </div>
            @endif
        @endif
        </div>
        @endif
    </section>

    <section class="space-y-3" aria-label="AI Assistant workflows">
        <div class="px-1">
            <h3 class="text-lg font-bold">Actions and reviews</h3>
            <p class="ai-muted text-sm">Expand only the workflow you need. The activity timeline is newest-first and can be shown or hidden above.</p>
        </div>

        <section class="ai-card ai-workflow" id="ai-workflow-analysis" wire:key="workflow-analysis">
            <button type="button" wire:click="toggleWorkflow('analysis')"
                aria-expanded="{{ in_array('analysis', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-analysis-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>01 · Latest case analysis and suggestions</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('analysis', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('analysis', $expandedWorkflows, true))
            <div id="ai-workflow-analysis-content" class="ai-workflow-content space-y-4">
                @if (is_array($result) && $analysis?->status === 'completed')
                    <p class="ai-muted text-xs">Completed {{ $analysis->updated_at?->timezone($timelineTimezone)->format('d M Y, H:i:s') }} {{ $timelineTimezone }}</p>
                    <div class="space-y-2">
                        <h4 class="text-sm font-semibold">Summary</h4>
                        <p class="whitespace-pre-line break-words text-sm">{{ $result['summary'] ?? 'No summary supplied.' }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border p-3" style="border-color:var(--ai-border)">
                            <p class="ai-muted text-xs">Suggested category</p>
                            <p class="mt-1 text-sm font-medium">{{ $result['suggested_category'] ?? 'No suggestion' }}</p>
                        </div>
                        <div class="rounded-lg border p-3" style="border-color:var(--ai-border)">
                            <p class="ai-muted text-xs">Suggested priority</p>
                            <p class="mt-1 text-sm font-medium">{{ $result['suggested_priority'] ?? 'No suggestion' }}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold">Suggested next actions · Not yet performed</h4>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @forelse (($result['suggested_next_actions'] ?? []) as $action)
                                <li>{{ $action }}</li>
                            @empty
                                <li class="ai-muted">No next actions suggested.</li>
                            @endforelse
                        </ul>
                    </div>
                    @if (!empty($result['warnings']) && is_array($result['warnings']))
                        <div>
                            <h4 class="text-sm font-semibold">Warnings / unknowns</h4>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                                @foreach ($result['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (!empty($result['customer_response_draft']))
                        <div>
                            <h4 class="text-sm font-semibold">Customer response draft · Not sent</h4>
                            <p class="mt-2 whitespace-pre-line break-words rounded-lg border p-3 text-sm" style="border-color:var(--ai-border)">{{ $result['customer_response_draft'] }}</p>
                        </div>
                    @endif
                @else
                    <p class="ai-muted text-sm">Run or complete a Case Assistant analysis to view recommendations here.</p>
                @endif
            </div>
        @endif
        </section>

        <section class="ai-card ai-workflow" id="ai-workflow-category" wire:key="workflow-category">
            <button type="button" wire:click="toggleWorkflow('category')"
                aria-expanded="{{ in_array('category', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-category-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>02 · Category recommendations and approvals</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('category', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('category', $expandedWorkflows, true))
            <div id="ai-workflow-category-content" class="ai-workflow-content">
                <livewire:ai-management.category-approvals :case="$case" :key="'category-approvals-'.$case->getKey()" />
            </div>
        @endif
        </section>

        <section class="ai-card ai-workflow" id="ai-workflow-engineer" wire:key="workflow-engineer">
            <button type="button" wire:click="toggleWorkflow('engineer')"
                aria-expanded="{{ in_array('engineer', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-engineer-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>03 · Engineer assignment</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('engineer', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('engineer', $expandedWorkflows, true))
            <div id="ai-workflow-engineer-content" class="ai-workflow-content">
                <livewire:ai-management.engineer-assignment-approvals :case="$case" :key="'engineer-approvals-'.$case->getKey()" />
            </div>
        @endif
        </section>

        <section class="ai-card ai-workflow" id="ai-workflow-start-work" wire:key="workflow-start-work">
            <button type="button" wire:click="toggleWorkflow('start-work')"
                aria-expanded="{{ in_array('start-work', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-start-work-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>04 · Start Work approvals</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('start-work', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('start-work', $expandedWorkflows, true))
            <div id="ai-workflow-start-work-content" class="ai-workflow-content">
                <livewire:ai-management.case-work-start-approvals :case="$case" :key="'work-start-approvals-'.$case->getKey()" />
            </div>
        @endif
        </section>

        <section class="ai-card ai-workflow" id="ai-workflow-troubleshoot" wire:key="workflow-troubleshoot">
            <button type="button" wire:click="toggleWorkflow('troubleshoot')"
                aria-expanded="{{ in_array('troubleshoot', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-troubleshoot-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>05 · Troubleshooting review</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('troubleshoot', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('troubleshoot', $expandedWorkflows, true))
            <div id="ai-workflow-troubleshoot-content" class="ai-workflow-content">
                <livewire:ai-management.troubleshooting-review :case="$case" :key="'troubleshooting-review-'.$case->getKey()" />
            </div>
        @endif
        </section>

        <section class="ai-card ai-workflow" id="ai-workflow-resolution" wire:key="workflow-resolution">
            <button type="button" wire:click="toggleWorkflow('resolution')"
                aria-expanded="{{ in_array('resolution', $expandedWorkflows, true) ? 'true' : 'false' }}"
                aria-controls="ai-workflow-resolution-content"
                class="ai-workflow-toggle flex w-full items-center justify-between gap-3 text-left">
                <span>06 · Resolution readiness and customer notification</span>
                <span aria-hidden="true" class="text-lg">{{ in_array('resolution', $expandedWorkflows, true) ? '−' : '+' }}</span>
            </button>
            @if (in_array('resolution', $expandedWorkflows, true))
            <div id="ai-workflow-resolution-content" class="ai-workflow-content">
                <livewire:ai-management.resolution-readiness :case="$case" :key="'resolution-readiness-'.$case->getKey()" />
            </div>
        @endif
        </section>
    </section>
</div>
