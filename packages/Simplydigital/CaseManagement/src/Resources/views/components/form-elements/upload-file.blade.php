<div>
    <form wire:submit="uploadAttachment">

        <x-casemanagement::form-elements.input-text model="attachment" type="file"/>

        @error('attachment')
            <div class="text-red-600">
                {{ $message }}
            </div>
        @enderror

        <div wire:loading wire:target="attachment">
            Uploading file to temporary storage...
        </div>

        <x-casemanagement::form-elements.button-primary  
            type="submit"
            label="Upload"
            :loadingAttributeFalse="true"
            loadingTarget="uploadAttachement"
        /> 

        <button type="submit" wire:loading.attr="disabled" wire:target="uploadAttachment">
            <span wire:loading.remove wire:target="uploadAttachment">
                Upload
            </span>

            <span wire:loading wire:target="uploadAttachment">
                Saving...
            </span>
        </button>

    </form>

    <div>
        @foreach ($this->attachments as $attachment)
            <div>
                {{ $attachment->original_name }}
            </div>
        @endforeach
    </div>

</div>
