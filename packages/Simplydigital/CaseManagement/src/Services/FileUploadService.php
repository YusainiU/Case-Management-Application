<?php

namespace Simplydigital\CaseManagement\Services;

use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Simplydigital\FileUploadManagement\Actions\DeleteFile;
use Simplydigital\FileUploadManagement\Actions\DownloadFile;
use Simplydigital\FileUploadManagement\Actions\MoveFile;
use Simplydigital\FileUploadManagement\Actions\UploadFile;
use Simplydigital\FileUploadManagement\Enums\FileOwnerType;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;

class FileUploadService
{
    public function __construct(
        private readonly UploadFile $uploadFile,
        private readonly DeleteFile $deleteFile,
        private readonly DownloadFile $downloadFile,
        private readonly MoveFile $moveFile,
    ) {
    }

    /**
     * Upload a file against a case.
     *
     * @throws FileUploadException
     */
    public function upload(
        string $caseNumber,
        HttpUploadedFile $file,
        ?int $uploadedBy = null,
        array $metadata = [],
    ): UploadedFile {
        return ($this->uploadFile)(
            ownerType: FileOwnerType::CASE,
            ownerIdentifier: $caseNumber,
            file: $file,
            uploadedBy: $uploadedBy,
            metadata: $metadata,
        );
    }

    /**
     * Delete a case file.
     *
     * @throws FileUploadException
     */
    public function delete(
        UploadedFile $file,
    ): bool {
        return ($this->deleteFile)($file);
    }

    /**
     * Download a case file.
     *
     * @throws FileUploadException
     */
    public function download(
        UploadedFile $file,
    ) {
        return ($this->downloadFile)($file);
    }

    /**
     * Move an existing file to another owner.
     *
     * This can be useful if a file initially uploaded against
     * a case subsequently needs to be associated with a customer.
     *
     * @throws FileUploadException
     */
    public function move(
        UploadedFile $file,
        FileOwnerType $ownerType,
        string $ownerIdentifier,
    ): UploadedFile {
        return ($this->moveFile)(
            uploadedFile: $file,
            ownerType: $ownerType,
            ownerIdentifier: $ownerIdentifier,
        );
    }

    /**
     * Get all files attached to a case.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, UploadedFile>
     */
    public function files(
        string $caseNumber,
    ) {
        return UploadedFile::query()
            ->where('owner_type', FileOwnerType::CASE->value)
            ->where('owner_identifier', $caseNumber)
            ->latest()
            ->get();
    }
}