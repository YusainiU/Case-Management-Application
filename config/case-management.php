<?php

declare(strict_types=1);

/**
 * Configuration template for SimplyDigital EngineerManager package.
 *
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Case Management Configuration
    |--------------------------------------------------------------------------
    */

    'CLOSE_TO_BREACH_WARNING' => 4, //Hours

    /*
    |--------------------------------------------------------------------------
    | Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for record numbers.
    |
    */
    
    'ASSET_PREFIX' => 'ASST',
    'SLA_PREFIX' => 'SLA',
    'TICKET_PREFIX' => 'CAS',
    'CUSTOMER_PREFIX' => 'CUST',
    'PRODUCT_PREFIX' => 'PROD',
    'SLA_APPLICATION_PREFIX' => 'SLAP',
    'SLA_TASK_PREFIX' => 'TASK',
    'SUPPLIER_PREFIX' => 'SUPP',
    'CUSTOMER_CONTACT_PREFIX' => 'CCT',
    'LOG_PREFIX' => 'LOG',
    'LOGVISIT_PREFIX' => 'LOGV', 

    /*
    |--------------------------------------------------------------------------
    | Customer Profile Tabs
    |--------------------------------------------------------------------------
    |
    | Tabs for customer profile 
    |
    */

    'customer_profile_tabs' => [
        'outstandingCases' => 'Outstanding Cases',
        'assets' => 'Assets',
        'contacts' => 'Contacts',
        'childs' => 'Child Companies',
        'sla' => 'Service Level Agreement',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticket Management Config
    |--------------------------------------------------------------------------
    */

    'ticketManagement' => [

        'state' => [
            'open' => 'Open',
            'closed' => 'Closed',
            'resolved' => 'Resolved',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
        ],

        'category' => [
            'Failure' => 'Failure',
            'Part Replacement' => 'Part Replacement',
            'Technical Support' => 'Technical Support',
        ],

        'source' => [
            'Phone' => 'Phone',
            'Email' => 'Email',
            'Portal' => 'Portal',
        ],
        'resolution' => [
            'No Fault Found' => 'No Fault Found',
            'Part Replacement' => 'Part Replacement',
            'User Error' => 'User Error',
            'Configuration Changes' => 'Configuration Changes',
            'Software Upgrade' => 'Software Upgrade',
            'Software Downgrade' => 'Software Downgrade',
            'System Reset' => 'System Reset',
            'Technical Support' => 'Technical Support',
            'System Features' => 'System Features',
            'Network Configuration' => 'Network Configuration',
            'No Resolution' => 'No Resolution',
        ],
    ],    
 

    /*
    |--------------------------------------------------------------------------
    | Log Management
    |--------------------------------------------------------------------------
    |
    | List of Log Types and Source
    |
    */
    
    'taskManagement' => [
        'type' => [
            'sla' => 'Service Level Agreement',
        ],
        'state' => [
            'Completed' => 'completed',
            'Paused' => 'paused',
            'In Progress' => 'in_progress',
            'Open' => 'open',
            'Resolved' => 'resolved',
        ],
    ],    

    'logs' => [
        'type' => [
            'system' => 'system_log',
            'error' => 'error_log',
            'internal_update' => 'internal_update_log',
            'external_update' => 'external_update_log',
            'sla_tasks' => 'sla_tasks_log',
            'ticket' => 'ticket_log',
            'resolution' => 'resolution',
            'completed' => 'completed',
            'site_visit' => 'site_visit',
            'site_visit_first' => 'site_visit_first',
            'site_visit_note' => 'site_visit_note',
            'site_visit_onsite' => 'site_visit_onsite',
            'site_visit_enroute' => 'site_visit_enroute',
            'site_visit_offsite' => 'site_visit_offsite',
        ],
        'source' => [
            'system' => 'system',
            'external' => 'external',
            'internal' => 'internal',
        ],
    ],
    
    'ProgressLogs' => [

        'create' => 'New Comment Created [%INFO%]: %NOTE% -- [%EXTRAINFO%]',
        'respondAt' => 'Responded at %RESPONSE% by %BY%',
        'fixedAt' => 'Fixed at %FIX% by %BY%',
        'breach' => 'The service level agreement has been breached at %BREACH%',
        'working' => '%USER% is currently working on the '.env('TICKET_MONIKER'),
        'unsetWorking' => '%USER% has removed the working flag',
        'assignedTo' => '%USER% assigned the '.env('TICKET_MONIKER').' to %ASSIGNEE% -- %DATETIME%',
        'unassigned' => '%USER% unassigned %ASSIGNEE% -- %DATETIME%',
        'email' => 'Progress Log %LOGNUMBER% has been emailed to %RECEIVER%',
        'complete' => env('TICKET_MONIKER').' closed by %USER%',
        'resolve' => '%USER% has resolved the case at %DATETIME%',
        'removeRequireAttention' => '%USER% removed required attention from %FROM%',
        'respondToComment' => '%USER% responded to the comment: %RESPONSE%',
        'pause' => '%USER% has paused the case at %DATETIME%',
        'resume' => '%USER% has resumed the case at %DATETIME%. Respond By and Fix By datetimes have been readjusted',
        'respondByTimeChanged' => 'Respond by time changed from %FROM% to %TO%',
        'cancelPause' => '%USER% has cancelled the pause of the case at %DATETIME%.',
        'reopen' => 'This case was previously closed under case number %CASENUMBER%',
    ],
    'siteVisit' => [
        'visitCreated' => 'Site Visit scheduled for %ASSIGNEE% by %SCHEDULER% on %DATETIME%',
        'scheduleUpdated' => 'Schedule for the visit has been updated by %SCHEDULER% at %DATETIME%',
        'scheduleRemoved' => 'Visit Schedule has been removed -- [%USER%]',
        'VisitNote' => 'Site Visit Note : %NOTE% -- [%CREATEDBY%]',
        'scheduledUpdated' => 'Scheduled Date %SCHEDULED% has been changed to %DATETIME% -- [%USER%]',
        'enrouteUpdated' => '%ASSIGNEE% enroute to site at %DATETIME% -- [%USER%]',
        'enrouteRemoved' => 'Enroute has been removed -- [%USER%]',
        'onsiteUpdated' => '%ASSIGNEE% is on site at %DATETIME% -- [%USER%]',
        'onsiteRemoved' => 'Onsite has been removed -- [%USER%]',
        'offsiteUpdate' => '%ASSIGNEE% is off site at %DATETIME% -- [%USER%]',
        'offsiteRemoved' => 'Off site has been removed -- [%USER%]',
        'assignedToUpdated' => 'Visit assignee has been changed from %CURRENT% to %NEW% -- [%USER%]',
        'assignedToRemove' => '%USER% removed visit assignment',
        'visitCompleted' => '%USER% has mark the visit as completed',
        'delete' => 'Site visit with a short description "%DESCRIPTION%" has been removed %DATETIME%-- [%USER%]',
    ],
    'ticket' => [
        'create' => env('TICKET_MONIKER').' created at %CREATED% by %BY%',
    ],
    'slaTask' => [
        'create' => 'SLA Task created at %CREATED%',
    ],    
        
];