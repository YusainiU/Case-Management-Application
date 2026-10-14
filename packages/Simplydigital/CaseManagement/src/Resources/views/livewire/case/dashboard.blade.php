
<div class="space-y-8">

    {{-- Page heading --}}
    <div>
        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
            CASE MANAGEMENT
        </p>

        <h1 class="mt-1 text-2xl font-semibold tracking-tight
                   text-gray-950 dark:text-white">
            Case Dashboard
        </h1>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Monitor your workload, SLA performance and case activity.
        </p>
    </div>

    {{-- Priority metrics --}}
    <section class="space-y-4">
        <div class="flex items-center gap-2">
            <flux:icon name="shield-exclamation"
                       class="size-5 text-red-500" />
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                Priority &amp; SLA
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['totalOpenCases']"
                title="SLA Breached"
                subTitle="Cases requiring urgent attention"
                action="openBreached"
                icon="exclamation-triangle"
                accent="red"
            />

            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['nearBreach']"
                title="Close to Expiry"
                subTitle="Approaching their SLA deadline"
                action="openNearBreach"
                icon="clock"
                accent="amber"
            />

            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['unassigned']"
                title="Unassigned"
                subTitle="Cases without an owner"
                action="openUnassigned"
                icon="user-minus"
                accent="blue"
            />
        </div>
    </section>

    {{-- Case status --}}
    <section class="space-y-4">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            Case Status
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['noUpdate']"
                title="No Recent Update"
                subTitle="Cases awaiting an update"
                action="openNoUpdate"
                icon="chat-bubble-left-right"
                accent="violet"
            />

            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['resolved']"
                title="Resolved"
                subTitle="Successfully resolved cases"
                action="openResolvedCases"
                icon="check-circle"
                accent="emerald"
            />

            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['paused']"
                title="Paused"
                subTitle="Cases with a paused SLA"
                action="openPaused"
                icon="pause-circle"
                accent="slate"
            />
        </div>
    </section>

    {{-- Workload --}}
    <section class="space-y-4">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            Workload &amp; Activity
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['total']"
                title="Total Outstanding"
                subTitle="All outstanding cases"
                action="openAllOutstanding"
                icon="folder-open"
                accent="indigo"
            />

            <x-casemanagement::page-elements.box-dashboard-element
                :content="$status['totalVisits']"
                title="Site Visits"
                subTitle="Recorded site visits"
                action="openVisit"
                icon="map-pin"
                accent="cyan"
            />
        </div>
    </section>

</div>
