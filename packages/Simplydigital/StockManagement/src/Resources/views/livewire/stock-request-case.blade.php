<div class="space-y-6 text-slate-800">
    @if ($message)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $message }}
        </div>
    @endif

    @can('create', [\Simplydigital\StockManagement\Models\StockRequest::class, $case])
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-slate-900">Request a part</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Submit the part and quantity needed for this case. The stock team will update the request as it is ordered and delivered.
                </p>
            </div>

            <form wire:submit="createStockRequest" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="partDescription" class="mb-1 block text-sm font-medium text-slate-700">Part description</label>
                        <textarea
                            id="partDescription"
                            wire:model="partDescription"
                            rows="3"
                            required
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        ></textarea>
                        @error('partDescription') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="quantity" class="mb-1 block text-sm font-medium text-slate-700">Quantity</label>
                        <input
                            id="quantity"
                            type="number"
                            min="1"
                            step="1"
                            wire:model="quantity"
                            required
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        />
                        @error('quantity') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="productId" class="mb-1 block text-sm font-medium text-slate-700">Catalog product (optional)</label>
                        <select
                            id="productId"
                            wire:model="productId"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->product_code }})</option>
                            @endforeach
                        </select>
                        @error('productId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="supplierId" class="mb-1 block text-sm font-medium text-slate-700">Preferred supplier (optional)</label>
                        <select
                            id="supplierId"
                            wire:model="supplierId"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">Not selected</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->number }})</option>
                            @endforeach
                        </select>
                        @error('supplierId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Request notes (optional)</label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="2"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        ></textarea>
                        @error('notes') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-4">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
                    >
                        Submit stock request
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <section class="space-y-4">
        <div>
            <h3 class="text-base font-semibold text-slate-900">Requests for this case</h3>
            <p class="mt-1 text-sm text-slate-500">Every status change is also recorded in the case progress log.</p>
        </div>

        @forelse ($stockRequests as $stockRequest)
            <article wire:key="stock-request-{{ $stockRequest->id }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $stockRequest->number }}</p>
                        <h4 class="mt-1 whitespace-pre-line text-sm font-semibold text-slate-900">{{ $stockRequest->part_description }}</h4>
                        <p class="mt-1 text-sm text-slate-600">
                            Quantity: {{ $stockRequest->quantity }}
                            @if ($stockRequest->product)
                                <span class="text-slate-400">-</span> {{ $stockRequest->product->name }}
                            @endif
                        </p>
                    </div>
                    <span @class([
                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-amber-100 text-amber-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Requested,
                        'bg-blue-100 text-blue-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Ordered,
                        'bg-indigo-100 text-indigo-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Dispatched,
                        'bg-emerald-100 text-emerald-800' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Received,
                        'bg-slate-100 text-slate-700' => $stockRequest->status === \Simplydigital\StockManagement\Enums\StockRequestStatus::Cancelled,
                    ])>
                        {{ $stockRequest->status->label() }}
                    </span>
                </div>

                <div class="space-y-3 px-4 py-4 sm:px-5">
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Requested by</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $stockRequest->requester?->name ?? 'Former user' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Supplier</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $stockRequest->supplier?->name ?? 'Not selected' }}</dd>
                        </div>
                        @if ($stockRequest->supplier_reference)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Supplier reference</dt>
                                <dd class="mt-0.5 text-slate-700">{{ $stockRequest->supplier_reference }}</dd>
                            </div>
                        @endif
                        @if ($stockRequest->tracking_reference)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tracking reference</dt>
                                <dd class="mt-0.5 text-slate-700">{{ $stockRequest->tracking_reference }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($stockRequest->notes)
                        <p class="whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $stockRequest->notes }}</p>
                    @endif

                    <div class="border-t border-slate-100 pt-3">
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Request history</h5>
                        <ol class="mt-3 space-y-3">
                            @foreach ($stockRequest->updates as $update)
                                <li wire:key="stock-request-update-{{ $update->id }}" class="flex gap-3">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500" aria-hidden="true"></span>
                                    <div class="min-w-0 text-sm">
                                        <p class="font-medium text-slate-800">
                                            {{ $update->to_status->label() }}
                                            <span class="font-normal text-slate-500">by {{ $update->user?->name ?? 'Former user' }}</span>
                                        </p>
                                        <p class="text-xs text-slate-500">{{ $update->created_at?->format('d M Y, H:i') }}</p>
                                        @if ($update->notes)
                                            <p class="mt-1 whitespace-pre-line text-slate-600">{{ $update->notes }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                <p class="text-sm font-medium text-slate-700">No stock requests for this case</p>
                <p class="mt-1 text-sm text-slate-500">New requests will appear here with their processing history.</p>
            </div>
        @endforelse
    </section>
</div>
