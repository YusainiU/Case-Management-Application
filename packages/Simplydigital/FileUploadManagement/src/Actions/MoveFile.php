<?php

namespace Simplydigital\FileUploadManagement\Actions;

use Simplydigital\FileUploadManagement\Enums\FileOwnerType;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\FileUploadManagement\Services\FileUploadService;

final class MoveFile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    /**
     * Move an uploaded file to another owner.
     *
     * The physical file is moved to the destination owner's
     * directory and the uploaded_files record is updated.
     *
     * Example:
     *
     * ($this->moveFile)(
     *     uploadedFile: $uploadedFile,
     *     ownerType: FileOwnerType::CUSTOMER,
     *     ownerIdentifier: $customer->customer_number,
     * );
     *
     * @throws FileUploadException
     */
    public function __invoke(
        UploadedFile $uploadedFile,
        FileOwnerType $ownerType,
        string $ownerIdentifier,
    ): UploadedFile {
        return $this->fileUploadService->move(
            uploadedFile: $uploadedFile,
            ownerType: $ownerType,
            ownerIdentifier: $ownerIdentifier,
        );
    }
}