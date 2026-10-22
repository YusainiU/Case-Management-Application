<div class="mx-auto max-w-3xl space-y-8">
    <a href="{{ route('portal.cases.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" wire:navigate>
        <flux:icon name="arrow-left" class="size-4" />
        Back to cases
    </a>

    <section class="space-y-2">
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Customer portal</p>
        <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Raise a case</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Tell us what you need help with. Your case will be visible to authorised contacts in your organisation.</p>
    </section>

    <form wire:submit="submit" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:field>
            <flux:label>Organisation</flux:label>
            <flux:select wire:model="customerId" placeholder="Select an organisation">
                @foreach ($customers as $customer)
                    <flux:select.option wire:key="customer-{{ $customer->id }}" value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="customerId" />
        </flux:field>


        <flux:field>
            <flux:label>Customer Reference</flux:label>
            <flux:input wire:model="customerReference" maxlength="50" placeholder="Your Reference Number" />
            <flux:error name="customerReference" />
        </flux:field>        

        <flux:field>
            <flux:label>Assets <span class="text-sm font-normal text-zinc-500">(required)</span></flux:label>
            @if ($hasAvailableAssets)
                <x-casemanagement::form-elements.multiselect
                    :options="$assetOptions"
                    placeholder="Select one or more assets"
                    wire:model="assetIds"
                />
            @elseif ($customerId)
                <p class="text-sm text-amber-700 dark:text-amber-400">
                    No active assets are available for this organisation. Contact support before raising a case.
                </p>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Select an organisation to see its available assets.
                </p>
            @endif
            <flux:error name="assetIds" />
            @foreach ($errors->get('assetIds.*') as $messages)
                @foreach ($messages as $message)
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @endforeach
            @endforeach
        </flux:field>

        <flux:field>
            <flux:label>Category</flux:label>
            <flux:select wire:model="category" placeholder="Select a category">
                @foreach ($categories as $value => $label)
                    <flux:select.option wire:key="category-{{ $value }}" value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="category" />
        </flux:field>

        <flux:field>
            <flux:label>Subject</flux:label>
            <flux:input wire:model="subject" maxlength="50" placeholder="Briefly describe the issue" />
            <flux:error name="subject" />
        </flux:field>

        <flux:field>
            <flux:label>Description</flux:label>
            <flux:textarea wire:model="description" rows="7" maxlength="10000" placeholder="Add details that will help us understand and resolve your request." />
            <flux:error name="description" />
        </flux:field>

        <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:justify-end dark:border-zinc-800">
            <flux:button href="{{ route('portal.cases.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Submit case</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </flux:button>
        </div>
    </form>
</div>
