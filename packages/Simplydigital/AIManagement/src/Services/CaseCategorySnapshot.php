<?php

namespace Simplydigital\AIManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;

final class CaseCategorySnapshot
{
    public static function hash(tickets $case): string
    {
        // Only the category-related context determines whether the proposal is stale.
        return hash('sha256', json_encode([
            'id' => $case->getKey(),
            'updated_at' => $case->getRawOriginal('updated_at'),
            'category' => $case->getRawOriginal('category'),
        ], JSON_THROW_ON_ERROR));
    }

    public static function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
