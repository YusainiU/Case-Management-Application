<?php

namespace Simplydigital\FileUploadManagement\Enums\Permissions;

enum FilePermission: string
{
    case View = 'file.view';
    case Create = 'file.create';
    case Update = 'file.update';
    case Delete = 'file.delete';

}