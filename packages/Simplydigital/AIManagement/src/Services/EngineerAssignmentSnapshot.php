<?php

namespace Simplydigital\AIManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;

final class EngineerAssignmentSnapshot
{
    public static function hash(tickets $case): string
    {
        return hash('sha256', json_encode([
            'id' => $case->getKey(),
            'updated_at' => $case->getRawOriginal('updated_at'),
            'assigned_to' => $case->getRawOriginal('assigned_to'),
            'category' => $case->getRawOriginal('category'),
        ], JSON_THROW_ON_ERROR));
    }
}
