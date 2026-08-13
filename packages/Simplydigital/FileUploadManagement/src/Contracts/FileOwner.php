<?php

namespace Simplydigital\FileUploadManagement\Contracts;

interface FileOwner
{
    /**
     * Get the type used to identify the owner.
     *
     * Examples:
     * - cases
     * - customers
     * - assets
     * - suppliers
     */
    public function getFileOwnerType(): string;

    /**
     * Get the unique identifier used for the owner's storage directory.
     *
     * Examples:
     * - CASE-000123
     * - CUST-000456
     */
    public function getFileOwnerIdentifier(): string;
}