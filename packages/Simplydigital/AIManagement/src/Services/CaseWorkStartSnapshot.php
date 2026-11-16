<?php

namespace Simplydigital\AIManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;

final class CaseWorkStartSnapshot
{
    public static function hash(tickets $case): string
    {
        return hash('sha256', json_encode([
            'id' => $case->getKey(),
            'updated_at' => $case->getRawOriginal('updated_at'),
            'state' => $case->getRawOriginal('state'),
            'assigned_to' => $case->assigned_to,
            'closed_datetime' => $case->closed_datetime,
            'deleted_at' => $case->deleted_at,
        ], JSON_THROW_ON_ERROR));
    }
}
