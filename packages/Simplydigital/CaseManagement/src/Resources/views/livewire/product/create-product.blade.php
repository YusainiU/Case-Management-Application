<div>
    {{-- <p>{{ dd(get_defined_vars()) }}</p> --}}
    <x-casemanagement::page-elements.modal title="{{ $ui_params['title'] }}" widthsize="6xl"
        cancelThisWith="closeThisModal" confirmText="{{ $ui_params['confirmText'] }}" confirm="save">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-casemanagement::form-elements.input-label name="Name" :required=true />
                <x-casemanagement::form-elements.input-text model="name" placeholder="Product Name" :required=true />
                <x-casemanagement::form-elements.input-error field="name" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Model" :required=false />
                <x-casemanagement::form-elements.input-text model="model" placeholder="Product Model" :required=false />
                <x-casemanagement::form-elements.input-error field="model" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Make" :required=false />
                <x-casemanagement::form-elements.input-text model="make" placeholder="Product Make" :required=false />
                <x-casemanagement::form-elements.input-error field="make" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Product Type" :required=true />
                <x-casemanagement::form-elements.select model="product_type" name="ProductType"
                    placeholder="Product Type" :options=$product_types />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Version" :required=false />
                <x-casemanagement::form-elements.input-text model="version" placeholder="Product Version"
                    :required=false />
                <x-casemanagement::form-elements.input-error field="version" />
            </div>

            <div>
                <x-casemanagement::form-elements.input-label name="Description" :required=false />
                <x-casemanagement::form-elements.textarea model="short_description" placeholder="Product Description"
                    :required=false hint="Product Description" />
                <x-casemanagement::form-elements.input-error field="short_description" />
            </div>

            <div class="flex flex-col gap-4 justify-items-start">
                <x-casemanagement::form-elements.checkbox name="Active" :value=$active model="active" label="Active" />
            </div>

        </div>
    </x-casemanagement::page-elements.modal>

</div>
