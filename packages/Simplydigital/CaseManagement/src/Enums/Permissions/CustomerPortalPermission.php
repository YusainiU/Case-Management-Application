<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum CustomerPortalPermission: string
{
    case Access = 'portal.access';
    case View = 'portal.view-case';
    case Create = 'portal.create-case';
    case Comment = 'portal.comment-case';
}