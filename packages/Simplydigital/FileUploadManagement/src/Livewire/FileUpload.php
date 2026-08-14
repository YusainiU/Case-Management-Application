<?php

namespace Simplydigital\FileUploadManagement\Livewire;

use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Simplydigital\FileUploadManagement\Actions\UploadFile;
use Simplydigital\FileUploadManagement\Enums\FileOwnerType;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;

class FileUpload extends Component
{
    use WithFileUploads;

    public FileOwnerType $ownerType;

    public string $ownerIdentifier;

    public ?HttpUploadedFile $file = null;

    public array $metadata = [];

    protected function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('fileupload.max_size', 10240),
            ],
        ];
    }

    public function mount(
        FileOwnerType $ownerType,
        string $ownerIdentifier,
    ): void {
        $this->ownerType = $ownerType;
        $this->ownerIdentifier = $ownerIdentifier;
    }

    public function upload(): void
    {
        $this->validate();
        try {
            $uploadFile = app(UploadFile::class);

            $uploadFile(
                ownerType: $this->ownerType,
                ownerIdentifier: $this->ownerIdentifier,
                file: $this->file,
                uploadedBy: auth()->id(),
                metadata: $this->metadata,
            );

            $this->reset('file', 'metadata');

            unset($this->uploadedFiles);

            session()->flash(
                'file-upload-success',
                'File uploaded successfully.'
            );

            $this->dispatch('file-uploaded');

        } catch (FileUploadException $exception) {
            report($exception);
            $this->addError(
                'file',
                'The file could not be uploaded. Please try again.'
            );
        }

    }

    #[Computed]
    public function uploadedFiles()
    {
        return UploadedFile::query()
            ->where(
                'owner_type',
                $this->ownerType->value
            )
            ->where(
                'owner_identifier',
                $this->ownerIdentifier
            )
            ->latest()
            ->get();
    }

    public function render()
    {
        return view(
            // 'fileupload-management::livewire.file-upload'
            'fileupload-management::livewire.file-upload'
        );
    }
}
