<?php

namespace Simplydigital\FileUploadManagement\DTO;

use Illuminate\Http\UploadedFile;

final readonly class UploadFileData
{
    public function __construct(
        public string $ownerType,
        public string $ownerIdentifier,
        public UploadedFile $file,
        public ?int $uploadedBy = null,
        public array $metadata = [],
    ) {
    }

    /**
     * Create an UploadFileData instance.
     */
    public static function make(
        string $ownerType,
        string $ownerIdentifier,
        UploadedFile $file,
        ?int $uploadedBy = null,
        array $metadata = [],
    ): self {
        return new self(
            ownerType: $ownerType,
            ownerIdentifier: $ownerIdentifier,
            file: $file,
            uploadedBy: $uploadedBy,
            metadata: $metadata,
        );
    }

    /**
     * Get the original filename.
     */
    public function originalName(): string
    {
        return $this->file->getClientOriginalName();
    }

    /**
     * Get the detected MIME type.
     */
    public function mimeType(): ?string
    {
        return $this->file->getMimeType();
    }

    /**
     * Get the original file extension.
     */
    public function extension(): ?string
    {
        return $this->file->getClientOriginalExtension() ?: null;
    }

    /**
     * Get the file size in bytes.
     */
    public function size(): int
    {
        return $this->file->getSize() ?: 0;
    }
}