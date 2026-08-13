<?php

namespace Simplydigital\FileUploadManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    /**
     * The database table associated with the model.
     */
    protected $table = 'uploaded_files';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'owner_type',
        'owner_identifier',
        'disk',
        'path',
        'original_name',
        'stored_name',
        'mime_type',
        'extension',
        'size',
        'checksum',
        'metadata',
        'uploaded_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'uploaded_by' => 'integer',
    ];

    /**
     * Get the user who uploaded the file.
     *
     * This intentionally does not enforce a foreign key at the
     * database level, keeping FileuploadManagement independent
     * from UserManagement.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            'uploaded_by'
        );
    }

    /**
     * Scope files belonging to an owner.
     */
    public function scopeForOwner(
        Builder $query,
        string $ownerType,
        string $ownerIdentifier
    ): Builder {
        return $query
            ->where('owner_type', $ownerType)
            ->where('owner_identifier', $ownerIdentifier);
    }

    /**
     * Scope files belonging to a particular owner type.
     */
    public function scopeOfType(
        Builder $query,
        string $ownerType
    ): Builder {
        return $query->where('owner_type', $ownerType);
    }

    /**
     * Scope files uploaded by a particular user.
     */
    public function scopeUploadedBy(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Determine whether the physical file exists.
     */
    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Get the file's public URL where supported by the configured disk.
     */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get a human-readable file size.
     */
    public function humanReadableSize(): string
    {
        $bytes = $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 ** 2) {
            return round($bytes / 1024, 1) . ' KB';
        }

        if ($bytes < 1024 ** 3) {
            return round($bytes / (1024 ** 2), 1) . ' MB';
        }

        return round($bytes / (1024 ** 3), 1) . ' GB';
    }

    /**
     * Get the file extension in uppercase.
     */
    public function formattedExtension(): ?string
    {
        return $this->extension
            ? strtoupper($this->extension)
            : null;
    }

    /**
     * Determine whether the file is an image.
     */
    public function isImage(): bool
    {
        return $this->mime_type !== null
            && str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Determine whether the file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get the original filename.
     */
    public function getOriginalFilename(): string
    {
        return $this->original_name;
    }

    /**
     * Get the stored filename.
     */
    public function getStoredFilename(): string
    {
        return $this->stored_name;
    }

    /**
     * Get the storage disk.
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get the storage path.
     */
    public function getPath(): string
    {
        return $this->path;
    }
}