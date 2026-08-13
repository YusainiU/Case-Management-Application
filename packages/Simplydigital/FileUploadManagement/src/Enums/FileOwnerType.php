<?php

namespace Simplydigital\FileUploadManagement\Enums;

enum FileOwnerType: string
{
    case CASE = 'Cases';
    case CUSTOMER = 'Customers';
    case ASSET = 'Assets';
    case SUPPLIER = 'Suppliers';

    /**
     * Get the storage directory name.
     */
    public function directory(): string
    {
        return $this->value;
    }
}