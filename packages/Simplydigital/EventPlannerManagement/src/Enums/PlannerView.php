<?php

namespace Simplydigital\EventPlannerManagement\Enums;

enum PlannerView: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Agenda = 'agenda';
    case List = 'list';
}
