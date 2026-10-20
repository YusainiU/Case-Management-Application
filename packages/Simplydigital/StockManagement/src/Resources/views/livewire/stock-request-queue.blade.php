<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Case Management</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Stock requests</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-zinc-300">Review engineer requests and record ordering, dispatch and receipt progress.</p>
        </div>
        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
            {{ $stockRequests->count() }} active {{ $stockRequests->count() === 1 ? 'request' : 'requests' }}
        </span>
    </header>

    @if ($message)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ $message }}
        </div>
    @endif

    @forelse ($stockRequests as $stockRequest)
        <article wire:key="stock-queue-request-{{ $stockRequest->id }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 p-4 dark:border-zinc-800 sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">{{ $stockRequest->number }}</span>
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-amber-100 text-amber-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Requested,
                            'bg-blue-100 text-blue-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Ordered,
                            'bg-indigo-100 text-indigo-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Dispatched,
                        ])>
                            {{ $stockRequest->status->label() }}
                        </span>
                    </div>
                    <h2 class="mt-2 whitespace-pre-line text-base font-semibold text-slate-900 dark:text-white">{{ $stockRequest->part_description }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-300">
                        Quantity: {{ $stockRequest->quantity }}
                        @if ($stockRequest->product)
                            <span class="text-slate-400">-</span> {{ $stockRequest->product->name }}
                        @endif
                    </p>
                </div>
                <div class="text-sm text-slate-600 dark:text-zinc-300">
                    <p><span class="font-medium text-slate-800 dark:text-zinc-100">Case:</span> {{ $stockRequest->case?->number ?? 'Unavailable' }}</p>
                    <p class="mt-1"><span class="font-medium text-slate-800 dark:text-zinc-100">Requested by:</span> {{ $stockRequest->requester?->name ?? 'Former user' }}</p>
                    <p class="mt-1"><span class="font-medium text-slate-800 dark:text-zinc-100">Submitted:</span> {{ $stockRequest->created_at?->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="space-y-3 p-4 sm:p-5">
                <dl class="grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Supplier</dt>
                        <dd class="mt-1 text-slate-800 dark:text-zinc-100">{{ $stockRequest->supplier?->name ?? 'Not selected' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Supplier reference</dt>
                        <dd class="mt-1 text-slate-800 dark:text-zinc-100">{{ $stockRequest->supplier_reference ?: 'Not recorded' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Tracking reference</dt>
                        <dd class="mt-1 text-slate-800 dark:text-zinc-100">{{ $stockRequest->tracking_reference ?: 'Not recorded' }}</dd>
                    </div>
                </dl>
                @if ($stockRequest->notes)
                    <p class="whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $stockRequest->notes }}</p>
                @endif

                @if ($selectedRequestId === $stockRequest->id)
                    <form wire:submit="saveProgress" class="space-y-4 rounded-lg border border-indigo-200 bg-indigo-50/60 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Update {{ $stockRequest->number }}</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="nextStatus" class="mb-1 block text-sm font-medium text-slate-700 dark:text-zinc-200">Move to</label>
                                <select id="nextStatus" wire:model="nextStatus" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                    @foreach ($nextStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                @error('nextStatus') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="supplierId" class="mb-1 block text-sm font-medium text-slate-700 dark:text-zinc-200">Supplier</label>
                                <select id="supplierId" wire:model="supplierId" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                    <option value="">Not selected</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->number }})</option>
                                    @endforeach
                                </select>
                                @error('supplierId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="supplierReference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-zinc-200">Supplier reference</label>
                                <input id="supplierReference" type="text" wire:model="supplierReference" maxlength="255" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" />
                                @error('supplierReference') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="trackingReference" class="mb-1 block text-sm font-medium text-slate-700 dark:text-zinc-200">Tracking reference</label>
                                <input id="trackingReference" type="text" wire:model="trackingReference" maxlength="255" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" />
                                @error('trackingReference') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="stageNotes" class="mb-1 block text-sm font-medium text-slate-700 dark:text-zinc-200">Update note (optional)</label>
                                <textarea id="stageNotes" wire:model="stageNotes" rows="2" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white"></textarea>
                                @error('stageNotes') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-end gap-2 border-t border-indigo-100 pt-3 dark:border-indigo-900">
                            <button type="button" wire:click="cancelEditing" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">Save progress</button>
                        </div>
                    </form>
                @else
                    <div class="flex justify-end border-t border-slate-100 pt-3 dark:border-zinc-800">
                        <button type="button" wire:click="selectRequest({{ $stockRequest->id }})" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Update request
                        </button>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">No active stock requests</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-zinc-300">Requests raised from a case will appear here.</p>
        </div>
    @endforelse
</div>
