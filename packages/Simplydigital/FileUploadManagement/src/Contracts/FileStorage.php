<?php

namespace Simplydigital\FileUploadManagement\Contracts;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\UploadedFile;

interface FileStorage
{
    /**
     * Store an uploaded file at the given path.
     *
     * @return string The path of the stored file.
     */
    public function put(
        string $path,
        UploadedFile $file,
    ): string;

    /**
     * Retrieve the contents of a stored file.
     */
    public function get(string $path): string;

    /**
     * Delete a stored file.
     */
    public function delete(string $path): bool;

    /**
     * Determine whether a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Generate a URL for a stored file.
     *
     * Depending on the underlying storage implementation,
     * this may be a public URL or a temporary/signed URL.
     */
    public function url(string $path): ?string;

    /**
     * Get the configured storage disk name.
     */
    public function disk(): string;

    /**
     * Get the size of a stored file in bytes.
     */
    public function size(string $path): int;

    /**
     * Get the MIME type of a stored file.
     */
    public function mimeType(string $path): ?string;

    public function download(
        string $path,
        ?string $name = null,
        array $headers = [],
    ): StreamedResponse;

    public function move(
        string $from,
        string $to,
    ): bool;
}
