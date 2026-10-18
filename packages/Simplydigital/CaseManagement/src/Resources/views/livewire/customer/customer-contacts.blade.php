<div class="grid grid-cols-1 gap-4 p-4">

    @if ($showCreateContactModal)
        <livewire:create-update-customer-contact :customerId="$customerId"
            @close-create-modal="closeCreateCustomerContactModal" />
    @endif

    @if ($showContactProfileModal)
        <livewire:userprofile-component :userId="$userProfileId" @close-user-profile="closeProfile" />
    @endif

    @if ($showContactUpdateModal)
        <livewire:update-customer-contact :customerContactId="$customerContactId" @close-update-modal="closeUpdateModal" />
    @endif

    <x-casemanagement::page-elements.page-header-section section="Customer Management" title="Customer Contacts"
        description="Manage your customer contacts here." :showCreateButton="true"
        createButtonLabel="Create New Customer Contact" createAction="openCreateContactModal" showBackButton="true" />

    {{-- Customer directory --}}
    <section class="">

        {{-- Directory header --}}
        <div
            class="flex flex-col gap-3 border-b border-slate-200
            px-5 py-4 sm:flex-row sm:items-center
            sm:justify-between sm:px-6">
            @if ($customerContacts)
                <div class="text-sm text-slate-500">
                    @if ($customerContacts->total() > 0)
                        Showing
                        <span class="font-semibold text-slate-700">
                            {{ $customerContacts->firstItem() }}–{{ $customerContacts->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700">
                            {{ $customerContacts->total() }}
                        </span>
                        customers Contacts
                    @else
                        No customer contacts found
                    @endif
                </div>
            @endif
        </div>

        {{-- Filters and pagination --}}
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <x-casemanagement::form-elements.input-filter />
            </div>

            @if ($customerContacts?->hasPages())
                <div class="text-slate-100!">
                    {{ $customerContacts->links('livewire::tailwind') }}
                </div>
            @endif
        </div>

        {{-- Customer directory table --}}
        <div class="">

            <div class="overflow-x-auto">
                <x-casemanagement::table-elements.table>
                    <thead>
                        <tr>
                            <x-casemanagement::table-elements.table-th>
                                #
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th sortable field="user_id" direction="asc" px="px-5">
                                Contact Name
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th sortable field="customer_id" direction="asc" px="px-5">
                                Customer
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th sortable field="email" direction="asc" px="px-5">
                                Email Address
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th sortable field="customerRole" direction="asc" px="px-5">
                                Contact Role
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th>
                                Phone
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th sortable field="has_portal_access" direction="asc" px="px-5">
                                Portal Access
                            </x-casemanagement::table-elements.table-th>

                            <x-casemanagement::table-elements.table-th>
                                <span class="sr-only">Actions</span>
                            </x-casemanagement::table-elements.table-th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @if ($customerContacts?->isNotEmpty())

                            @foreach ($customerContacts as $contact)
                                <tr wire:key="customer-contact-{{ $contact->id }}"
                                    class="bg-white transition-colors hover:bg-slate-50">

                                    {{-- Row number --}}
                                    <x-casemanagement::table-elements.table-td>
                                        <span
                                            class="text-xs font-medium tabular-nums text-slate-800">
                                            {{ $customerContacts->firstItem() + $loop->index }}
                                        </span>
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Contact name --}}
                                    <x-casemanagement::table-elements.table-td>
                                        <div class="flex min-w-40 items-center gap-3">

                                            <x-casemanagement::page-elements.name-initial-icon name="{{ $contact->customerUser?->name }}" />

                                            <div class="min-w-0">
                                                <button type="button"
                                                    wire:click="openContactProfileModal({{ $contact->customerUser?->id }})"
                                                    class="max-w-full truncate text-left text-sm font-semibold text-slate-800 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:hover:text-indigo-400 dark:focus:ring-offset-slate-900">
                                                    {{ $contact->customerUser?->name ?? 'Unknown contact' }}
                                                </button>
                                                <p class="text-xs text-slate-600 ">
                                                    Contact ID: {{ $contact->id }}
                                                </p>
                                            </div>
                                        </div>
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Customer --}}
                                    <x-casemanagement::table-elements.table-td>
                                        <span class="text-sm font-medium text-slate-800">
                                            {{ $contact->customer?->name ?? '—' }}
                                        </span>
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Email --}}
                                    <x-casemanagement::table-elements.table-td>
                                        @if ($contact->customerUser?->email)
                                            <a href="mailto:{{ $contact->customerUser->email }}"
                                                class="text-sm text-slate-800 hover:text-indigo-600 hover:underline dark:hover:text-indigo-400">
                                                {{ $contact->customerUser->email }}
                                            </a>
                                        @else
                                            <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
                                        @endif
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Contact role --}}
                                    <x-casemanagement::table-elements.table-td>
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 font-medium text-slate-800">
                                            {{ $contact->customerRole?->label() ?? 'N/A' }}
                                        </span>
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Phone --}}
                                    <x-casemanagement::table-elements.table-td>
                                        @if ($contact->customerUserDetails?->phone)
                                            <span class="whitespace-nowrap text-sm text-slate-800">
                                                {{ $contact->customerUserDetails->phone }}
                                            </span>
                                        @else
                                            <span class="text-sm text-slate-600 ">—</span>
                                        @endif
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Portal access --}}
                                    <x-casemanagement::table-elements.table-td>
                                        @if ($contact->has_portal_access)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 ">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Enabled
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-500/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                Disabled
                                            </span>
                                        @endif
                                    </x-casemanagement::table-elements.table-td>

                                    {{-- Actions --}}
                                    <x-casemanagement::table-elements.table-td>
                                        <x-casemanagement::form-elements.button-edit action="openUpdateContactModal({{ $contact->id }})" label="Edit" />
                                    </x-casemanagement::table-elements.table-td>

                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <x-casemanagement::page-elements.empty-state 
                                        title=" No customer contacts found" 
                                        description="Customer contacts will appear here when they are available." 
                                    />
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </x-casemanagement::table-elements.table>
            </div>
        </div>

        {{-- Bottom pagination --}}
        @if ($customerContacts->hasPages())
            <div class="px-5 py-4 sm:px-6">
                {{ $customerContacts->links() }}
            </div>
        @endif

    </section>

</div>
