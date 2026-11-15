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
            .ai-assistant-shell .ai-workflow > summary {
                cursor: pointer;
                list-style-position: inside;
                padding: 1rem;
                font-weight: 650;
                color: var(--ai-ink);
            }
            .ai-assistant-shell .ai-workflow > summary:focus-visible,
            .ai-assistant-shell button:focus-visible,
            .ai-assistant-shell a:focus-visible {
                outline: 3px solid var(--ai-accent);
                outline-offset: 3px;
            }
            .ai-assistant-shell .ai-workflow[open] > summary { border-bottom: 1px solid var(--ai-border); }
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

    <section class="ai-card p-4 sm:p-5" aria-label="AI activity timeline" id="ai-activity-timeline">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold">Activity timeline</h3>
                <p class="ai-muted text-sm">Recent AI actions, decisions, troubleshooting and resolution events.</p>
            </div>
            <span class="ai-muted rounded-md border px-2 py-1 text-xs" style="border-color:var(--ai-border)">
                Times shown in {{ $timelineTimezone }}
            </span>
        </div>

        @if ($timelineEntries->isEmpty())
            <div class="ai-muted mt-5 rounded-lg border border-dashed p-5 text-sm" style="border-color:var(--ai-border)">
                No AI activity has been recorded in Case Progress yet. New events will appear here automatically.
                Older events may require the optional AI progress backfill.
            </div>
        @else
            <ol class="ai-timeline mt-5 space-y-4" aria-label="Newest AI actions first">
                @foreach ($timelineEntries as $entry)
                    @php
                        $source = (string) $entry->source;
                        $key = (string) ($entry->short_description ?? '');
                        $event = $source === 'ai_agent' ? IlluminateSupportStr::afterLast($key, ':') : null;
                        $actionName = $source === 'ai_agent'
                            ? (IlluminateSupportStr::before((string) $entry->description, ' — ') ?: 'AI Agent')
                            : ($source === 'ai_assisted' ? 'Troubleshooting review' : 'Resolution handoff');
                        $heading = $source === 'ai_agent'
                            ? (IlluminateSupportStr::startsWith($key, 'AI:resolution-notification:')
                                ? 'Customer notification · '.ucfirst($event)
                                : (IlluminateSupportStr::startsWith($key, 'AI:analysis:')
                                    ? 'Case Assistant · '.ucfirst($event)
                                    : $actionName.' · '.ucfirst($event)))
                            : $actionName.' saved';
                        $statusColor = match ($event) {
                            'completed', 'sent', 'approved' => 'text-emerald-700 dark:text-emerald-300',
                            'failed', 'rejected', 'stale' => 'text-red-700 dark:text-red-300',
                            'pending', 'queued', 'requested' => 'text-amber-700 dark:text-amber-300',
                            default => 'ai-muted',
                        };
                        $date = $entry->created_at?->timezone($timelineTimezone);
                    @endphp
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
                            @if ($entry->description)
                                <p class="mt-2 whitespace-pre-line break-words text-sm">{{ IlluminateSupportStr::limit($entry->description, 260) }}</p>
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
    </section>

    <section class="space-y-3" aria-label="AI Assistant workflows">
        <div class="px-1">
            <h3 class="text-lg font-bold">Actions and reviews</h3>
            <p class="ai-muted text-sm">Expand only the workflow you need. The timeline above always shows the most recent activity first.</p>
        </div>

        <details class="ai-card ai-workflow" id="ai-workflow-analysis" @if ($analysis && in_array($analysis->status, ['pending','processing'], true)) open @endif>
            <summary>01 · Latest case analysis and suggestions</summary>
            <div class="ai-workflow-content space-y-4">
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
        </details>

        <details class="ai-card ai-workflow" id="ai-workflow-category">
            <summary>02 · Category recommendations and approvals</summary>
            <div class="ai-workflow-content">
                <livewire:ai-management.category-approvals :case="$case" :key="'category-approvals-'.$case->getKey()" />
            </div>
        </details>

        <details class="ai-card ai-workflow" id="ai-workflow-engineer">
            <summary>03 · Engineer assignment</summary>
            <div class="ai-workflow-content">
                <livewire:ai-management.engineer-assignment-approvals :case="$case" :key="'engineer-approvals-'.$case->getKey()" />
            </div>
        </details>

        <details class="ai-card ai-workflow" id="ai-workflow-start-work">
            <summary>04 · Start Work approvals</summary>
            <div class="ai-workflow-content">
                <livewire:ai-management.case-work-start-approvals :case="$case" :key="'work-start-approvals-'.$case->getKey()" />
            </div>
        </details>

        <details class="ai-card ai-workflow" id="ai-workflow-troubleshoot">
            <summary>05 · Troubleshooting review</summary>
            <div class="ai-workflow-content">
                <livewire:ai-management.troubleshooting-review :case="$case" :key="'troubleshooting-review-'.$case->getKey()" />
            </div>
        </details>

        <details class="ai-card ai-workflow" id="ai-workflow-resolution">
            <summary>06 · Resolution readiness and customer notification</summary>
            <div class="ai-workflow-content">
                <livewire:ai-management.resolution-readiness :case="$case" :key="'resolution-readiness-'.$case->getKey()" />
            </div>
        </details>
    </section>
</div>
