<x-casemanagement::layouts.portal-auth>
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-9 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-2">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Customer access</p>
            <h2 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Sign in to your portal</h2>
            <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">Use the email address linked to your customer contact account.</p>
        </div>

        <x-auth-session-status class="mt-5 text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="mt-7 flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="username"
                :value="old('email')"
                placeholder="you@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="portal-login-button">
                Sign in to customer portal
            </flux:button>
        </form>

        <div class="mt-6 border-t border-zinc-200 pt-5 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
            Internal team member?
            <flux:link :href="route('login')" wire:navigate>Sign in to the staff portal</flux:link>
        </div>
    </div>
</x-casemanagement::layouts.portal-auth>
