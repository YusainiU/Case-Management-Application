<?php

namespace Simplydigital\EngineerManagement\Enums\Permissions;

enum CertificatePermission: string
{
    case View = 'certificate.view';
    case Create = 'certificate.create';
    case Update = 'certificate.update';
    case Delete = 'certificate.delete';

}