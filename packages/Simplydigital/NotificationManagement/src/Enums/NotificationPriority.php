<?php

namespace Simplydigital\NotificationManagement\Enums;

enum NotificationPriority : int
{
    case Low = 10;

    case Normal = 50;

    case High = 75;

    case Urgent = 100;

    case Critical = 255;
}