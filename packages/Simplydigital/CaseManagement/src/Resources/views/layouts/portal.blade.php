<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('portal.dashboard') }}" class="text-lg font-semibold tracking-tight text-zinc-950 dark:text-white" wire:navigate>
                    Customer Support
                </a>

                <nav aria-label="Customer portal" class="flex flex-wrap items-center gap-2 text-sm font-medium">
                    <a
                        href="{{ route('portal.dashboard') }}"
                        @class([
                            'rounded-lg px-3 py-2',
                            'bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300' => request()->routeIs('portal.dashboard'),
                            'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => ! request()->routeIs('portal.dashboard'),
                        ])
                        wire:navigate
                    >
                        Overview
                    </a>
                    <a
                        href="{{ route('portal.cases.index') }}"
                        @class([
                            'rounded-lg px-3 py-2',
                            'bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300' => request()->routeIs('portal.cases.*'),
                            'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => ! request()->routeIs('portal.cases.*'),
                        ])
                        wire:navigate
                    >
                        My organisation's cases
                    </a>
                </nav>

                <div class="flex items-center gap-4">
                    <span class="hidden text-sm text-zinc-600 dark:text-zinc-300 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:button type="submit" variant="ghost" size="sm">Sign out</flux:button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
