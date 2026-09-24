<?php

namespace Simplydigital\AIManagement\Enums;

enum AIStatuses: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}