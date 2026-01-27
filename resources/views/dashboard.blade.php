@php
    $allEngineers = "allEngineers";
    $allCertificates = "allCertificates";
    $allSkills = "allSkills";
    $expiredCertifications = "expiredCertifications";
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-6xl flex items-center justify-center h-64">
                    <livewire:engineer-management-counter :selectCounter="$allEngineers" />
                </div>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-6xl flex items-center justify-center h-64">
                    <livewire:engineer-management-counter :selectCounter="$allCertificates" />
                </div>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-6xl flex items-center justify-center h-64">
                    <livewire:engineer-management-counter :selectCounter="$allSkills" />
                </div>                
            </div>
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-6xl flex items-center justify-center h-64">
                    <livewire:engineer-management-counter :selectCounter="$expiredCertifications" />
                </div>
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <livewire:engineers-summary />
        </div>
    </div>
</x-layouts.app>
