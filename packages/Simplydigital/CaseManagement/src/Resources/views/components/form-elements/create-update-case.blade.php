<div class="space-y-7">

    {{-- Customer and case details --}}
    <section>
        <div class="mb-5 flex items-start gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <flux:icon name="clipboard-document-list" class="size-5" />
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Case details
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Identify the customer, title and service requirements.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-casemanagement::form-elements.input-label
                    name="Customer Selected"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-text
                    value="{{ $customerSelected->name ?? '' }}"
                    disabled
                    :required="false"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Case Title"
                    :required="true"
                />
                <x-casemanagement::form-elements.input-text
                    model="form.short_description"
                    placeholder="Enter a short case title"
                    maxlength="50"
                    :required="true"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.short_description"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Select SLA"
                    :required="true"
                />
                <x-casemanagement::form-elements.select
                    model="form.sla_applications_id"
                    name="Service Level Agreement"
                    placeholder="Select an SLA"
                    :options="$slapOptions"
                    :disabled="$modeUpdate"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.sla_applications_id"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Category"
                    :required="true"
                />
                <x-casemanagement::form-elements.select
                    model="form.category"
                    name="Category"
                    placeholder="Select a category"
                    :options="$categoryOptions"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.category"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Source"
                    :required="true"
                />
                <x-casemanagement::form-elements.select
                    model="form.source"
                    name="Source"
                    placeholder="Select a source"
                    :options="$sourceOptions"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.source"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Select Assets"
                    :required="false"
                />
                <x-casemanagement::form-elements.multiselect
                    :options="$assetOptions"
                    placeholder="Select associated assets"
                    wire:model="form.list_of_assets"
                />
            </div>
        </div>
    </section>

    <div class="border-t border-gray-200 dark:border-gray-700"></div>

    {{-- Contact details --}}
    <section>
        <div class="mb-5 flex items-start gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <flux:icon name="user-circle" class="size-5" />
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Contact information
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Record who raised the case and how they can be contacted.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-casemanagement::form-elements.input-label
                    name="Customer Contact (User)"
                    :required="false"
                />
                <x-casemanagement::form-elements.select
                    model="form.raised_by_user"
                    name="Customer Contact User"
                    placeholder="Select a contact"
                    :options="$contactOptions"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.raised_by_user"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Customer Contact (Non-User)"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-text
                    model="form.raised_by_nonuser"
                    placeholder="Enter contact name"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.raised_by_nonuser"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Telephone"
                    :required="true"
                />
                <x-casemanagement::form-elements.input-text
                    model="form.contact_telephone"
                    placeholder="Enter telephone number"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.contact_telephone"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Email"
                    :required="true"
                />
                <x-casemanagement::form-elements.input-text
                    model="form.contact_email"
                    placeholder="Enter email address"
                    :required="true"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.contact_email"
                />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label
                    name="Customer Reference"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-text
                    model="form.customer_reference"
                    placeholder="Enter customer reference"
                    :required="false"
                />
                <x-casemanagement::form-elements.input-error
                    field="form.customer_reference"
                />
            </div>
        </div>
    </section>

    <div class="border-t border-gray-200 dark:border-gray-700"></div>

    {{-- Description --}}
    <section>
        <div class="mb-5 flex items-start gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <flux:icon name="document-text" class="size-5" />
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Case description
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Describe the issue, request or work required.
                </p>
            </div>
        </div>

        <x-casemanagement::form-elements.textarea
            model="form.description"
            placeholder="Provide details about the case..."
            :required="true"
            hint="Include relevant symptoms, steps already taken and the expected outcome."
        />

        <x-casemanagement::form-elements.input-error
            field="form.description"
        />
    </section>

    {{-- Update action --}}
    @if(isset($modeUpdate) && $modeUpdate && !$case->closed_datetime)
        <div class="flex justify-end border-t border-gray-200 pt-5 dark:border-gray-700">
            <x-casemanagement::form-elements.button-primary
                action="updateCase"
                label="Update Case"
            />
        </div>
    @endif

</div>