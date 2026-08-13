<?php

namespace Simplydigital\FileUploadManagement\Actions;

use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\FileUploadManagement\Services\FileUploadService;

final class DeleteFile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    /**
     * Delete an uploaded file.
     *
     * This removes both:
     *
     * 1. The physical file from the configured storage disk.
     * 2. The corresponding uploaded_files database record.
     *
     * Example:
     *
     * $deleted = ($this->deleteFile)($uploadedFile);
     *
     * @throws FileUploadException
     */
    public function __invoke(
        UploadedFile $uploadedFile,
    ): bool {
        return $this->fileUploadService->delete(
            $uploadedFile
        );
    }
}