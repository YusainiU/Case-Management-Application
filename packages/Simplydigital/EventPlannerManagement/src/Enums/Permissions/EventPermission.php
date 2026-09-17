<?php

namespace Simplydigital\EventPlannerManagement\Enums\Permissions;

enum EventPermission: string
{
    case View = 'event.view';
    case Create = 'event.create';
    case Update = 'event.update';
    case Delete = 'event.delete';

}