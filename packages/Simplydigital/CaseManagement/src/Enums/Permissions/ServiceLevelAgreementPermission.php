<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum ServiceLevelAgreementPermission: string
{
    case View = 'service_level_agreement.view';
    case Create = 'service_level_agreement.create';
    case Update = 'service_level_agreement.update';
    case Delete = 'service_level_agreement.delete';
}
