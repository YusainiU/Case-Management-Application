<div>
    @if ($displayForm)
        @if ($modeUpdate)
            @include('casemanagement::components.form-elements.create-update-case')
        @else
            <x-casemanagement::page-elements.modal
                :title="$ui_params['title']"
                :widthsize="$ui_params['widthsize']"
                :cancelThisWith="$ui_params['cancelThisWith']"
                :confirmText="$ui_params['confirmText']"
                :confirm="$ui_params['confirm']"
            >
                @include('casemanagement::components.form-elements.create-update-case')
            </x-casemanagement::page-elements.modal>
        @endif
    @else
        <x-casemanagement::page-elements.modal
            :title="$ui_params['title']"
            :widthsize="$ui_params['widthsize']"
            :cancelThisWith="$ui_params['cancelThisWith']"
            :showFooter="false"
        >
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <flux:icon name="building-office" class="size-5" />
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Select a customer
                        </h3>
                        <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">
                            Choose the customer or site that this case relates to.
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50/70 p-5">
                    <x-casemanagement::form-elements.searchable-select
                        name="Customer"
                        :required="true"
                        :options="$customerOptions"
                        wireModel="customerId"
                    />
                </div>

                <div class="flex justify-end border-t border-gray-200 pt-5 dark:border-gray-700">
                    <x-casemanagement::form-elements.button-primary
                        label="Next"
                        action="selectCustomer"
                    />
                </div>
            </div>
        </x-casemanagement::page-elements.modal>
    @endif
</div>
