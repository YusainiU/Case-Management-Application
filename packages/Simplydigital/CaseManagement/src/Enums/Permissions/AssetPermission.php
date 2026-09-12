<?php

namespace Simplydigital\CaseManagement\Enums\Permissions;

enum AssetPermission: string
{
    case View = 'asset.view';
    case Create = 'asset.create';
    case Update = 'asset.update';
    case Delete = 'asset.delete';

}
