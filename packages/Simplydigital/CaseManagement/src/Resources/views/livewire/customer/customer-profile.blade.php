<div class="light space-y-6 bg-slate-50 p-4 text-slate-900 sm:p-6">

    {{-- Modals --}}
    @if ($showUpdateModal)
        <livewire:create-customer :customerId="$customerId" />
    @endif

    @if ($showConfirmDelete)
        <livewire:delete-customer :customerId="$customerId" />
    @endif

    {{-- Page heading --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">
                        Customer Management
                    </span>

                    <span @class([
                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' => $customer->active,
                        'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200' => ! $customer->active,
                    ])>
                        <span @class([
                            'h-1.5 w-1.5 rounded-full',
                            'bg-emerald-500' => $customer->active,
                            'bg-slate-400' => ! $customer->active,
                        ])></span>

                        {{ $customer->active ? 'Active Account' : 'Inactive Account' }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Customer Profile
                </h1>

                @if (filled($customer->short_description))
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        {{ $customer->short_description }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-500">
                        View and manage customer details, contacts, assets and service information.
                    </p>
                @endif
            </div>

            {{-- Page actions --}}
            <div class="flex flex-wrap items-center gap-2">
                <x-casemanagement::form-elements.button-primary
                    label="Customers List"
                    action="viewCustomerList"
                />

                <x-casemanagement::form-elements.button-primary
                    label="Update Details"
                    action="displayUpdate"
                />

                <x-casemanagement::form-elements.button-danger
                    label="Delete"
                    action="confirmDelete"
                />
            </div>

        </div>
    </section>

    {{-- Customer overview --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">
                    Customer Information
                </h2>
                <p class="mt-1 text-xs text-slate-700">
                    Customer profile and account details
                </p>
            </div>

            <div class="p-5">
                <x-casemanagement::page-elements.customer-info
                    :customer="$customer"
                />
            </div>
        </div>

        <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">
                    Address Information
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Customer address and location details
                </p>
            </div>

            <div class="p-5">
                <x-casemanagement::page-elements.customer-address
                    :customer="$customer"
                    :customerAddress="$customerAddress"
                />
            </div>
        </div>

    </section>

    {{-- Customer-related records --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-5 pt-5">
            <h2 class="text-base font-semibold text-slate-900">
                Customer Records
            </h2>
            <p class="mt-1 pb-4 text-sm text-slate-500">
                Manage contacts, related companies, assets, service agreements and cases.
            </p>

            <div class="-mx-1 overflow-x-auto pb-px">
                <x-casemanagement::page-elements.tab
                    :tabHeaders="$tabHeaders"
                    :selectedTab="$selectedTab"
                >
                    <x-slot name="tabSelectedComponent">
                        Selected Tab
                    </x-slot>
                </x-casemanagement::page-elements.tab>
            </div>
        </div>

        <div class="p-4 sm:p-6">

            @if ('contacts' === $selectedTab)
                <livewire:customer-contacts :customerId="$customerId" />
            @endif

            @if ('childs' === $selectedTab)
                <livewire:view-customer :viewChildCompanies="$customerId" />
            @endif

            @if ('assets' === $selectedTab)
                <livewire:view-assets :customerId="$customerId" />
            @endif

            @if ('sla' === $selectedTab)
                <livewire:view-sla-application :customerId="$customerId" />
            @endif

            @if ('outstandingCases' === $selectedTab)
                <livewire:view-cases :customerId="$customerId" />
            @endif

            @if ('upload' === $selectedTab)
                <livewire:customer-files :customer="$customer" />
            @endif

        </div>
    </section>

</div>