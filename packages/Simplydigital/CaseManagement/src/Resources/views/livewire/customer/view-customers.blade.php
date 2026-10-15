<div class="space-y-6 p-4 sm:p-6 lg:p-8">

    {{-- Customer modals --}}
    @if ($displayCreateModal)
        <livewire:create-customer />
    @endif

    @if ($displayUpdateModal)
        <livewire:create-customer :customerId="$customerId" />
    @endif


    <x-casemanagement::page-elements.page-header-section section="Customer Management" title="View Customers"
        description="Manage your customer here." :showCreateButton="true" createButtonLabel="Create New Customer"
        createAction="openCreateModal" showBackButton="true" />

    {{-- Customer directory --}}
    <section class="overflow-hidden rounded-2xl shadow-sm">
        {{-- Directory header --}}
        <div
            class="flex flex-col gap-3 border-b border-slate-200
                   px-5 py-4 sm:flex-row sm:items-center
                   sm:justify-between sm:px-6">

            @if ($customers)
                <div class="text-sm text-slate-500">
                    @if ($customers->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $customers->firstItem() }}–{{ $customers->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $customers->total() }}
                        </span>
                        customers
                    @else
                        No customer records found
                    @endif
                </div>
            @endif
        </div>

        {{-- Filters and pagination --}}
        <div class="flex flex-col gap-4 border-b border-slate-100
                   px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($customers?->hasPages())
                <div>
                    {{ $customers->links('livewire::tailwind') }}
                </div>
            @endif
        </div>

        {{-- Customer table --}}
        <x-casemanagement::table-elements.table mt="mt-0" mb="mb-0" px="px-0" py="py-0"
            border="border-0" whitespace="whitespace-nowrap">
            <thead>
                <tr>
                    <x-casemanagement::table-elements.table-th px="px-5" align="text-left">
                        #
                    </x-casemanagement::table-elements.table-th>

                    <x-casemanagement::table-elements.table-th sortable field="name" direction="asc" px="px-5">
                        Customer
                    </x-casemanagement::table-elements.table-th>

                    <x-casemanagement::table-elements.table-th sortable field="primary_type" direction="asc"
                        px="px-5">
                        Primary type
                    </x-casemanagement::table-elements.table-th>

                    <x-casemanagement::table-elements.table-th px="px-5">
                        Parent company
                    </x-casemanagement::table-elements.table-th>

                    <x-casemanagement::table-elements.table-th px="px-5">
                        Portal access
                    </x-casemanagement::table-elements.table-th>

                    <x-casemanagement::table-elements.table-th px="px-5" align="text-right">
                        Actions
                    </x-casemanagement::table-elements.table-th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">

                @forelse ($customers ?? [] as $customer)
                    <tr wire:key="customer-row-{{ $customer->id }}"
                        class="transition-colors duration-150
                               hover:bg-slate-50/80">
                        {{-- Row number --}}
                        <x-casemanagement::table-elements.table-td px="px-5" textcolor="text-slate-400">
                            <span class="text-xs font-medium tabular-nums">
                                {{ ($customers?->firstItem() ?? 1) + $loop->index }}
                            </span>
                        </x-casemanagement::table-elements.table-td>

                        {{-- Customer name --}}
                        <x-casemanagement::table-elements.table-td px="px-5" textcolor="text-slate-900">
                            <div class="flex items-center gap-3">

                                <x-casemanagement::page-elements.name-initial-icon name="{{ $customer->name }}" />

                                <div class="min-w-0">
                                    <a href="{{ route('casemanagement.customer.profile', ['customerId' => $customer->id]) }}"
                                        class="font-semibold text-slate-800
                                               transition hover:text-indigo-700
                                               focus:outline-none focus:underline">
                                        {{ $customer->name }}
                                    </a>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Customer #{{ $customer->id }}
                                    </p>
                                </div>
                            </div>
                        </x-casemanagement::table-elements.table-td>

                        {{-- Primary type --}}
                        <x-casemanagement::table-elements.table-td px="px-5">
                            <span
                                class="inline-flex items-center rounded-md
                                       bg-slate-100 px-2.5 py-1 text-xs
                                       font-medium text-slate-700">
                                {{ $customer->primary_type?->label() ?? 'Not specified' }}
                            </span>
                        </x-casemanagement::table-elements.table-td>

                        {{-- Parent company --}}
                        <x-casemanagement::table-elements.table-td px="px-5">
                            @if ($customer->parentCompany)
                                <span class="text-sm font-medium text-slate-700">
                                    {{ $customer->parentCompany->name }}
                                </span>
                            @else
                                <span class="text-sm text-slate-400">
                                    No parent company
                                </span>
                            @endif
                        </x-casemanagement::table-elements.table-td>

                        {{-- Portal status --}}
                        <x-casemanagement::table-elements.table-td px="px-5">
                            @if ($customer->portal_enabled)
                                <span
                                    class="inline-flex items-center gap-1.5
                                           rounded-full bg-emerald-50
                                           px-2.5 py-1 text-xs font-medium
                                           text-emerald-700 ring-1
                                           ring-inset ring-emerald-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Enabled
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5
                                           rounded-full bg-slate-100
                                           px-2.5 py-1 text-xs font-medium
                                           text-slate-600 ring-1
                                           ring-inset ring-slate-500/10">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Disabled
                                </span>
                            @endif
                        </x-casemanagement::table-elements.table-td>

                        {{-- Actions --}}
                        <x-casemanagement::table-elements.table-td px="px-5" align="text-right">
                            <x-casemanagement::form-elements.button-edit action="openUpdateModal({{ $customer->id }})"
                                label="Edit" />
                        </x-casemanagement::table-elements.table-td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                        <td colspan="8" class="px-6 py-16 text-center">
                            <x-casemanagement::page-elements.empty-state 
                                title="No customers found"
                                description="There are no customer records to display. Try adjusting your filters or create a new customer." 
                            />
                        </td>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </x-casemanagement::table-elements.table>

        {{-- Bottom pagination --}}
        @if ($customers?->hasPages())
            <div class="px-5 py-4 sm:px-6">
                {{ $customers->links() }}
            </div>
        @endif
    </section>

</div>
