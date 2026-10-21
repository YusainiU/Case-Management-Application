<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Services\FileUploadService;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile as UploadedAttachment;

class CustomerFiles extends Component
{

    use WithFileUploads;

    public Customer $customer;
    public ?UploadedFile $attachment = null;
    public ?UploadedAttachment $uploadedAttachment = null;
    
    public function mount()
    {
        $this->authorize('view', $this->customer);
    }

    public function removeAttachment(UploadedAttachment $attachment)
    {
        return app(FileUploadService::class)->delete($attachment);
    }

    public function downloadAttachment(UploadedAttachment $attachment)
    {
        return app(FileUploadService::class)->download($attachment);
    }

    public function getAttachmentsProperty()
    {
        if (! $this->customer) {
            return collect();
        }

        return app(FileUploadService::class)
            ->files($this->customer->number, 'Customers');
    }    

    public function uploadAttachment(): void
    {

        $this->authorize('update', $this->customer);
        $this->authorize('create', UploadedAttachment::class);

        $this->validate([
            'attachment' => [
                'required',
                'file',
                'max:10240',
            ],
        ]);

        if (! $this->customer) {
            $this->addError(
                'attachment',
                'A customer must be selected before uploading a file.'
            );
            return;
        }

        try {
            app(FileUploadService::class)->upload(
                ownerType:'Customers',
                ownerNumber: $this->customer->number,
                file: $this->attachment,
                uploadedBy: auth()->id(),
            );

            $this->reset('attachment');

            $this->dispatch('file-uploaded');

            session()->flash(
                'file-upload-success',
                'File uploaded successfully.'
            );
        } catch (FileUploadException $exception) {
            report($exception);
            $this->addError(
                'attachment',
                'The file could not be uploaded.'
            );
        }
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.customer-files');
    }


}