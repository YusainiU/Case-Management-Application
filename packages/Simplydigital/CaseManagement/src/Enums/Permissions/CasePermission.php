<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum CasePermission: string
{
    case View = 'case.view';
    case Create = 'case.create';
    case Update = 'case.update';
    case Delete = 'case.delete';
    case Assign = 'case.assign';
    case Close = 'case.close';
}
