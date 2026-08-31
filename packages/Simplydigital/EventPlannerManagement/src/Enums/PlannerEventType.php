<?php

namespace Simplydigital\EventPlannerManagement\Enums;

enum PlannerEventType: string
{
    case Case = 'case';
    case SlaExpiry = 'sla_expiry';
    case SiteVisit = 'site_visit';
    case Appointment = 'appointment';
    case Reminder = 'reminder';
    case Custom = 'custom';
}
