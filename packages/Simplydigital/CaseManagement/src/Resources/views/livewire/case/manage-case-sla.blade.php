<div>

    <div class="grid grid-cols-3 gap-4 mb-4">        
        <div>
            <x-casemanagement::form-elements.input-label name="Responded At" :required=false />
            <x-casemanagement::form-elements.input-text 
                type="datetime-local"
                model="form.responded_at"
                name="Responded At"
                bgcolor="bg-gray-500"
                textcolor="text-gray-100"
                bgfocus="focus:bg-gray-400"
            />
        </div>
        <div>
            <x-casemanagement::form-elements.input-label name="Fixed At" :required=false />
            <x-casemanagement::form-elements.input-text 
                type="datetime-local"
                model="form.fixed_at"
                name="Fixed At"
                bgcolor="bg-gray-500"
                textcolor="text-gray-100"
                bgfocus="focus:bg-gray-400"
            />
        </div>

        </div>
    </div>      
</div>