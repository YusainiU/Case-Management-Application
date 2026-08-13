<?php

namespace Simplydigital\FileUploadManagement\Actions;

use Illuminate\Http\Response;
use Illuminate\Http\StreamedResponse;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\FileUploadManagement\Services\FileUploadService;

final class DownloadFile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    /**
     * Download an uploaded file.
     *
     * The file is streamed from the configured storage disk
     * rather than loading the entire file into memory.
     *
     * Example:
     *
     * return ($this->downloadFile)($uploadedFile);
     *
     * @throws FileUploadException
     */
    public function __invoke(
        UploadedFile $uploadedFile,
    ): StreamedResponse|Response {
        return $this->fileUploadService->download(
            $uploadedFile
        );
    }
}