<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-svh bg-zinc-50 antialiased dark:bg-zinc-950">
        <main class="mx-auto grid min-h-svh max-w-7xl items-center gap-10 px-5 py-8 sm:px-8 lg:grid-cols-2 lg:gap-16 lg:px-12">
            <section class="flex flex-col gap-10">
                <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-3" wire:navigate>
                    <x-app-logo-icon class="h-auto w-44" />
                    <span class="sr-only">{{ config('app.name', 'Laravel') }} customer support</span>
                </a>

                <div class="relative isolate overflow-hidden rounded-3xl bg-linear-to-br from-blue-950 via-blue-900 to-indigo-800 px-7 py-10 text-white shadow-xl sm:px-10 sm:py-12 lg:min-h-105 lg:content-center">
                    <div aria-hidden="true" class="absolute -end-16 -top-20 -z-10 size-64 rounded-full border border-white/10"></div>
                    <div aria-hidden="true" class="absolute -end-4 -top-8 -z-10 size-40 rounded-full border border-white/10"></div>

                    <div class="flex size-12 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                        <flux:icon name="lifebuoy" class="size-6 text-blue-100" />
                    </div>

                    <p class="mt-8 text-sm font-semibold uppercase tracking-[0.2em] text-blue-200">Customer support portal</p>
                    <h1 class="mt-4 max-w-lg text-3xl font-semibold tracking-tight sm:text-4xl">
                        Support that keeps you in the loop.
                    </h1>
                    <p class="mt-4 max-w-lg text-base leading-7 text-blue-100/85">
                        Raise a request, follow its progress, and keep up with replies from your support team in one place.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 border-t border-white/15 pt-6 text-sm text-blue-100 sm:flex-row sm:gap-8">
                        <div class="flex items-center gap-2">
                            <flux:icon name="check-circle" class="size-4 shrink-0 text-blue-200" />
                            Organisation-wide case history
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon name="chat-bubble-left-right" class="size-4 shrink-0 text-blue-200" />
                            Customer-visible updates
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-md">
                {{ $slot }}
            </section>
        </main>

        @fluxScripts
    </body>
</html>
