<?php

namespace Simplydigital\FileUploadManagement\Services;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Simplydigital\FileUploadManagement\Contracts\FileStorage;
use Simplydigital\FileUploadManagement\DTO\UploadFileData;
use Simplydigital\FileUploadManagement\Exceptions\FileStorageException;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\FileUploadManagement\Enums\FileOwnerType;

class FileUploadService
{
    public function __construct(
        private readonly FileStorage $storage,
        private readonly FilePathResolver $pathResolver,
    ) {}

    /**
     * Upload a file and create its database record.
     *
     * @throws FileUploadException
     */
    public function upload(
        UploadFileData $data,
    ): UploadedFile {
        $storedName = $this->generateStoredName(
            $data->extension()
        );

        $path = $this->pathResolver->file(
            ownerType: $data->ownerType,
            ownerIdentifier: $data->ownerIdentifier,
            storedName: $storedName,
        );

        /*
         * ---------------------------------------------------------
         * 1. Store the physical file
         * ---------------------------------------------------------
         *
         * FileStorageManager is responsible for translating
         * filesystem errors into FileStorageException.
         *
         * FileUploadService translates that into the higher-level
         * FileUploadException.
         */
        try {
            $storedPath = $this->storage->put(
                path: $path,
                file: $data->file,
            );
        } catch (FileStorageException $exception) {
            throw FileUploadException::failed(
                ownerType: $data->ownerType,
                ownerIdentifier: $data->ownerIdentifier,
                originalName: $data->originalName(),
                previous: $exception,
            );
        }

        /*
         * ---------------------------------------------------------
         * 2. Create the database record
         * ---------------------------------------------------------
         */
        try {
            return UploadedFile::create([
                'owner_type' => $data->ownerType,
                'owner_identifier' => $data->ownerIdentifier,

                'disk' => $this->storage->disk(),
                'path' => $storedPath,

                'original_name' => $data->originalName(),
                'stored_name' => $storedName,

                'mime_type' => $data->mimeType(),
                'extension' => $data->extension(),
                'size' => $data->size(),

                'uploaded_by' => $data->uploadedBy,

                'metadata' => $data->metadata,
            ]);
        } catch (\Throwable $exception) {

            /*
             * The file exists physically, but the database record
             * could not be created.
             *
             * We must attempt to remove the physical file.
             */
            try {
                $this->storage->delete($storedPath);
            } catch (FileStorageException $cleanupException) {

                /*
                 * At this point we have two failures:
                 *
                 * 1. Database record creation failed.
                 * 2. Cleanup of the physical file failed.
                 *
                 * The cleanup exception is useful for logging,
                 * but the main operation that failed was the
                 * database record creation.
                 */
                $exception = $cleanupException;
            }

            throw FileUploadException::databaseRecordFailed(
                originalName: $data->originalName(),
                ownerType: $data->ownerType,
                ownerIdentifier: $data->ownerIdentifier,
                previous: $exception,
            );
        }
    }

    /**
     * Delete an uploaded file and its database record.
     *
     * @throws FileUploadException
     */
    public function delete(
        UploadedFile $uploadedFile,
    ): bool {
        try {
            $deleted = $this->storage->delete(
                $uploadedFile->path
            );
        } catch (FileStorageException $exception) {
            throw FileUploadException::failed(
                ownerType: $uploadedFile->owner_type,
                ownerIdentifier: $uploadedFile->owner_identifier,
                originalName: $uploadedFile->original_name,
                previous: $exception,
            );
        }

        if (! $deleted) {
            throw FileUploadException::failed(
                ownerType: $uploadedFile->owner_type,
                ownerIdentifier: $uploadedFile->owner_identifier,
                originalName: $uploadedFile->original_name,
            );
        }

        try {
            $uploadedFile->delete();
        } catch (\Throwable $exception) {
            throw FileUploadException::databaseRecordFailed(
                originalName: $uploadedFile->original_name,
                ownerType: $uploadedFile->owner_type,
                ownerIdentifier: $uploadedFile->owner_identifier,
                previous: $exception,
            );
        }

        return true;
    }

    /**
     * Generate the physical filename.
     */
    protected function generateStoredName(
        ?string $extension,
    ): string {
        $filename = (string) Str::ulid();

        if ($extension !== null && $extension !== '') {
            $filename .= '.'.strtolower($extension);
        }

        return $filename;
    }

    /**
     * Download an uploaded file.
     *
     * @throws FileUploadException
     */
    public function download(
        UploadedFile $uploadedFile,
    ): StreamedResponse|Response {
        if (! $this->storage->exists($uploadedFile->path)) {
            throw FileUploadException::failed(
                ownerType: $uploadedFile->owner_type,
                ownerIdentifier: $uploadedFile->owner_identifier,
                originalName: $uploadedFile->original_name,
            );
        }

        try {
            return $this->storage->download(
                path: $uploadedFile->path,
                name: $uploadedFile->original_name,
                headers: [
                    'Content-Type' => $uploadedFile->mime_type
                        ?? 'application/octet-stream',
                ],
            );
        } catch (FileStorageException $exception) {
            throw FileUploadException::failed(
                ownerType: $uploadedFile->owner_type,
                ownerIdentifier: $uploadedFile->owner_identifier,
                originalName: $uploadedFile->original_name,
                previous: $exception,
            );
        }
    }

/**
 * Move an uploaded file to another owner.
 *
 * The physical file is moved first. The database record is
 * updated only after the physical move succeeds.
 *
 * @throws FileUploadException
 */
public function move(
    UploadedFile $uploadedFile,
    FileOwnerType $ownerType,
    string $ownerIdentifier,
): UploadedFile {
    $newOwnerType = $ownerType->value;

    /*
     * Nothing to do if the file already belongs to this owner.
     */
    if (
        $uploadedFile->owner_type === $newOwnerType &&
        $uploadedFile->owner_identifier === $ownerIdentifier
    ) {
        return $uploadedFile;
    }

    /*
     * Keep the existing physical filename.
     *
     * Example:
     *
     * uploads/Cases/CASE-000123/01KXYZ.pdf
     *
     * becomes:
     *
     * uploads/Customers/CUST-000456/01KXYZ.pdf
     */
    $storedName = $uploadedFile->file_name;

    $newPath = $this->pathResolver->file(
        ownerType: $newOwnerType,
        ownerIdentifier: $ownerIdentifier,
        storedName: $storedName,
    );

    /*
     * Make sure the source file actually exists.
     */
    if (! $this->storage->exists($uploadedFile->path)) {
        throw FileUploadException::failed(
            ownerType: $uploadedFile->owner_type,
            ownerIdentifier: $uploadedFile->owner_identifier,
            originalName: $uploadedFile->original_name,
        );
    }

    /*
     * Move the physical file.
     */
    try {
        $this->storage->move(
            from: $uploadedFile->path,
            to: $newPath,
        );
    } catch (FileStorageException $exception) {
        throw FileUploadException::failed(
            ownerType: $uploadedFile->owner_type,
            ownerIdentifier: $uploadedFile->owner_identifier,
            originalName: $uploadedFile->original_name,
            previous: $exception,
        );
    }

    /*
     * Physical move succeeded.
     *
     * Now update the database record.
     */
    try {
        $uploadedFile->update([
            'owner_type' => $newOwnerType,
            'owner_identifier' => $ownerIdentifier,
            'path' => $newPath,
        ]);
    } catch (\Throwable $exception) {
        /*
         * The database update failed after the physical move.
         *
         * Attempt to move the file back to its original location.
         */
        try {
            $this->storage->move(
                from: $newPath,
                to: $uploadedFile->path,
            );
        } catch (FileStorageException $rollbackException) {
            report($rollbackException);
        }

        throw FileUploadException::databaseRecordFailed(
            originalName: $uploadedFile->original_name,
            ownerType: $newOwnerType,
            ownerIdentifier: $ownerIdentifier,
            previous: $exception,
        );
    }

    return $uploadedFile->refresh();
}    
}
