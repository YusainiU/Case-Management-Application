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

    'event_types' => [

        'case' => [
            'label' => 'Cases',
            'colour' => '#3B82F6',
            'icon' => 'briefcase',
        ],

        'sla_expiry' => [
            'label' => 'SLA Expiry',
            'colour' => '#EF4444',
            'icon' => 'clock',
        ],

        'site_visit' => [
            'label' => 'Site Visits',
            'colour' => '#10B981',
            'icon' => 'map-pin',
        ],

        'appointment' => [
            'label' => 'Appointments',
            'colour' => '#8B5CF6',
            'icon' => 'calendar',
        ],

        'reminder' => [
            'label' => 'Reminders',
            'colour' => '#F59E0B',
            'icon' => 'bell',
        ],

    ],

    'default_range_days' => 30,

];
