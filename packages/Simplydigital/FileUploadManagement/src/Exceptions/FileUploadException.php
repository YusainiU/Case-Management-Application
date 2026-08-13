<?php

namespace Simplydigital\FileUploadManagement\Exceptions;

use RuntimeException;
use Throwable;

class FileUploadException extends RuntimeException
{
    /**
     * The owner type associated with the upload.
     */
    public readonly ?string $ownerType;

    /**
     * The owner identifier associated with the upload.
     */
    public readonly ?string $ownerIdentifier;

    /**
     * The original filename, when available.
     */
    public readonly ?string $originalName;

    public function __construct(
        string $message,
        ?string $ownerType = null,
        ?string $ownerIdentifier = null,
        ?string $originalName = null,
        ?Throwable $previous = null,
    ) {
        $this->ownerType = $ownerType;
        $this->ownerIdentifier = $ownerIdentifier;
        $this->originalName = $originalName;

        parent::__construct(
            message: $message,
            code: 0,
            previous: $previous,
        );
    }

    /**
     * Create an exception for a general upload failure.
     */
    public static function failed(
        ?string $ownerType = null,
        ?string $ownerIdentifier = null,
        ?string $originalName = null,
        ?Throwable $previous = null,
    ): self {
        $message = 'Unable to upload file.';

        if ($originalName !== null) {
            $message .= sprintf(
                ' File: "%s".',
                $originalName
            );
        }

        if (
            $ownerType !== null &&
            $ownerIdentifier !== null
        ) {
            $message .= sprintf(
                ' Owner: %s/%s.',
                $ownerType,
                $ownerIdentifier
            );
        }

        return new self(
            message: $message,
            ownerType: $ownerType,
            ownerIdentifier: $ownerIdentifier,
            originalName: $originalName,
            previous: $previous,
        );
    }

    /**
     * Create an exception when the uploaded file is invalid.
     */
    public static function invalidFile(
        string $originalName,
        ?string $reason = null,
        ?Throwable $previous = null,
    ): self {
        $message = sprintf(
            'Invalid file "%s".',
            $originalName
        );

        if ($reason !== null) {
            $message .= ' ' . $reason;
        }

        return new self(
            message: $message,
            originalName: $originalName,
            previous: $previous,
        );
    }

    /**
     * Create an exception when the database record could not be created.
     */
    public static function databaseRecordFailed(
        string $originalName,
        ?string $ownerType = null,
        ?string $ownerIdentifier = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'The file "%s" was stored, but its database record could not be created.',
                $originalName
            ),
            ownerType: $ownerType,
            ownerIdentifier: $ownerIdentifier,
            originalName: $originalName,
            previous: $previous,
        );
    }

    /**
     * Create an exception when cleanup after a failed upload fails.
     */
    public static function cleanupFailed(
        string $path,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'The file upload failed and the stored file "%s" could not be cleaned up.',
                $path
            ),
            previous: $previous,
        );
    }
}