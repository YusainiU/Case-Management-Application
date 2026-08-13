<?php

namespace Simplydigital\FileUploadManagement\Services;

//
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Simplydigital\FileUploadManagement\Contracts\FileStorage;
use Simplydigital\FileUploadManagement\Exceptions\FileStorageException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStorageManager implements FileStorage
{
    private FilesystemAdapter $storage;

    public function __construct(
        private readonly string $disk,
    ) {
        $this->storage = Storage::disk($this->disk);
    }

    /**
     * Store an uploaded file at the given path.
     *
     * @throws RuntimeException
     */
    public function put(
        string $path,
        UploadedFile $file,
    ): string {
        try {
            $storedPath = $this->storage->putFileAs(
                path: dirname($path),
                file: $file,
                name: basename($path),
            );
        } catch (\Throwable $exception) {
            throw FileStorageException::uploadFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }

        if ($storedPath === false) {
            throw FileStorageException::uploadFailed(
                disk: $this->disk,
                path: $path,
            );
        }

        return $storedPath;
    }

    /**
     * Retrieve the contents of a stored file.
     *
     * @throws RuntimeException
     */
    public function get(string $path): string
    {
        try {
            $contents = $this->storage->get($path);
        } catch (\Throwable $exception) {
            throw FileStorageException::retrievalFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }

        return $contents;
    }

    /**
     * Delete a stored file.
     */
    public function delete(string $path): bool
    {
        try {
            $deleted = $this->storage->delete($path);
        } catch (\Throwable $exception) {
            throw FileStorageException::deletionFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }

        if (! $deleted) {
            throw FileStorageException::deletionFailed(
                disk: $this->disk,
                path: $path,
            );
        }

        return true;
    }

    /**
     * Determine whether a file exists.
     */
    public function exists(string $path): bool
    {
        return $this->storage->exists($path);
    }

    /**
     * Generate a URL for a stored file.
     *
     * Not every filesystem provides a usable URL. In those cases
     * null is returned.
     */
    public function url(string $path): ?string
    {
        try {
            return $this->storage->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get the configured storage disk name.
     */
    public function disk(): string
    {
        return $this->disk;
    }

    /**
     * Get the size of a stored file in bytes.
     *
     * @throws RuntimeException
     */
    public function size(string $path): int
    {
        if (! $this->exists($path)) {
            throw FileStorageException::notFound(
                disk: $this->disk,
                path: $path,
            );
        }

        try {
            return $this->storage->size($path);
        } catch (\Throwable $exception) {
            throw FileStorageException::retrievalFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }
    }

    /**
     * Get the MIME type of a stored file.
     */
    public function mimeType(string $path): ?string
    {
        if (! $this->exists($path)) {
            return null;
        }

        try {
            return $this->storage->mimeType($path);
        } catch (\Throwable $exception) {
            throw FileStorageException::retrievalFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }
    }

    public function temporaryUrl(
        string $path,
        \DateTimeInterface $expiration,
    ): ?string {
        try {
            if (! method_exists($this->storage, 'temporaryUrl')) {
                return null;
            }

            return $this->storage->temporaryUrl(
                $path,
                $expiration,
            );
        } catch (\Throwable $exception) {
            throw FileStorageException::retrievalFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }
    }

    public function download(
        string $path,
        ?string $name = null,
        array $headers = [],
    ): StreamedResponse {
        try {
            return $this->storage->download(
                $path,
                $name,
                $headers,
            );
        } catch (\Throwable $exception) {
            throw FileStorageException::retrievalFailed(
                disk: $this->disk,
                path: $path,
                previous: $exception,
            );
        }
    }

    public function move(
        string $from,
        string $to,
    ): bool {
        try {
            $moved = $this->storage->move(
                $from,
                $to,
            );
        } catch (\Throwable $exception) {
            throw FileStorageException::moveFailed(
                disk: $this->disk,
                from: $from,
                to: $to,
                previous: $exception,
            );
        }

        if (! $moved) {
            throw FileStorageException::uploadFailed(
                disk: $this->disk,
                path: $to,
            );
        }

        return true;
    }
}
