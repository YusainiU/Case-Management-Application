<?php

namespace Simplydigital\FileUploadManagement\Exceptions;

use RuntimeException;
use Throwable;

class FileStorageException extends RuntimeException
{
    /**
     * The storage disk involved in the failure.
     */
    public readonly ?string $disk;

    /**
     * The path involved in the failure.
     */
    public readonly ?string $path;

    public function __construct(
        string $message,
        ?string $disk = null,
        ?string $path = null,
        ?Throwable $previous = null,
    ) {
        $this->disk = $disk;
        $this->path = $path;

        parent::__construct(
            message: $message,
            code: 0,
            previous: $previous,
        );
    }

    /**
     * Create an exception for a failed file upload.
     */
    public static function uploadFailed(
        string $disk,
        string $path,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'Unable to store file "%s" on disk "%s".',
                $path,
                $disk
            ),
            disk: $disk,
            path: $path,
            previous: $previous,
        );
    }

    /**
     * Create an exception for a failed file retrieval.
     */
    public static function retrievalFailed(
        string $disk,
        string $path,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'Unable to retrieve file "%s" from disk "%s".',
                $path,
                $disk
            ),
            disk: $disk,
            path: $path,
            previous: $previous,
        );
    }

    /**
     * Create an exception for a failed file deletion.
     */
    public static function deletionFailed(
        string $disk,
        string $path,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'Unable to delete file "%s" from disk "%s".',
                $path,
                $disk
            ),
            disk: $disk,
            path: $path,
            previous: $previous,
        );
    }

    /**
     * Create an exception for a missing file.
     */
    public static function notFound(
        string $disk,
        string $path,
    ): self {
        return new self(
            message: sprintf(
                'File "%s" does not exist on disk "%s".',
                $path,
                $disk
            ),
            disk: $disk,
            path: $path,
        );
    }

    public static function moveFailed(
        string $disk,
        string $from,
        string $to,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: sprintf(
                'Unable to move file from "%s" to "%s" on disk "%s".',
                $from,
                $to,
                $disk
            ),
            disk: $disk,
            path: $to,
            previous: $previous,
        );
    }
}
