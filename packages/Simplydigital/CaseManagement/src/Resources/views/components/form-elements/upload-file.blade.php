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

    </form>

    <h1 class="mt-5 text-gray-900 dark:text-gray-100 text-2xl border-b-2 border-gray-400">Uploaded Files</h1>
    
    <div class="grid grid-cols-1 gap-2 text-gray-900 mt-2 dark:text-gray-200">
        @foreach ($this->attachments as $attachment)
            <div class="grid w-full grid-cols-2 border-gray-400 border-b-2 p-3 border-dotted">
                <div class="text-left">
                    <span class="font-extrabold">{{ $attachment->original_name }}</span>
                    [ 
                        <span class="italic">Uploaded At: {{ $attachment->created_at->format('d-m-Y H:i') }}
                            By {{ $attachment->uploader->name }}
                        </span>
                    ]
                </div>
                <div class="text-right">
                    <div class="flex flex-rows gap-2 justify-end">
                        @can('file.create', $uploadedAttachment)
                            <x-casemanagement::form-elements.button-danger action="removeAttachment({{ $attachment->id }})" label="Remove"/>
                            <x-casemanagement::form-elements.button-secondary action="downloadAttachment({{ $attachment->id }})" label="Download"/>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
        @if($this->attachments->isEmpty())
            <span class="text-center italic">[ Empty ]</span>
        @endif
    </div>

</div>
