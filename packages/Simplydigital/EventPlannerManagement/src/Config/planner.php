<?php

return [
    'default_view' => 'week',

    'available_views' => [
        'day',
        'week',
        'month',
        'agenda',
        'list',
    ],

    'event_types' => array_map(
        static fn ($type) => $type->value,
        \Simplydigital\EventPlannerManagement\Enums\PlannerEventType::cases()
    ),

    'default_range_days' => 30,
];
