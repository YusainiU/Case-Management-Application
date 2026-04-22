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

    /*
    |--------------------------------------------------------------------------
    | Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for record numbers.
    |
    */
    
    'ASSET_PREFIX' => 'ASSET',
    'SLA_PREFIX' => 'SLA',
    'TICKET_PREFIX' => 'TICKET',
    'CUSTOMER_PREFIX' => 'CUST',
    'PRODUCT_PREFIX' => 'PROD',
    'SLA_APPLICATION_PREFIX' => 'SLAAPP',
    'SLA_TASK_PREFIX' => 'SLATASK',
    'SUPPLIER_PREFIX' => 'SUPP',
    'CUSTOMER_CONTACT_PREFIX' => 'CUSTCONT',

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
    ],
    
];