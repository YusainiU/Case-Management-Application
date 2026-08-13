<?php

namespace Simplydigital\FileUploadManagement\Actions;

use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Simplydigital\FileUploadManagement\DTO\UploadFileData;
use Simplydigital\FileUploadManagement\Enums\FileOwnerType;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\FileUploadManagement\Services\FileUploadService;

final class UploadFile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    /**
     * Upload a file for an owner.
     *
     * Example:
     *
     * $uploadedFile = ($uploadFile)(
     *     ownerType: FileOwnerType::CASE,
     *     ownerIdentifier: $case->case_number,
     *     file: $file,
     *     uploadedBy: auth()->id(),
     * );
     *
     * @throws FileUploadException
     */
    public function __invoke(
        FileOwnerType $ownerType,
        string $ownerIdentifier,
        HttpUploadedFile $file,
        ?int $uploadedBy = null,
        array $metadata = [],
    ): UploadedFile {
        $data = UploadFileData::make(
            ownerType: $ownerType->value,
            ownerIdentifier: $ownerIdentifier,
            file: $file,
            uploadedBy: $uploadedBy,
            metadata: $metadata,
        );

        return $this->fileUploadService->upload($data);
    }

    /**
     * Upload using an existing UploadFileData DTO.
     *
     * This is useful when the DTO has already been constructed
     * by another application service, Livewire component, or
     * package.
     *
     * @throws FileUploadException
     */
    public function fromData(
        UploadFileData $data,
    ): UploadedFile {
        return $this->fileUploadService->upload($data);
    }
}