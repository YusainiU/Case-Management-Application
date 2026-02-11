<?php

declare(strict_types=1);

/**
 * Configuration template for SimplyDigital UserManager package.
 *
 * Copy this file to your application's config directory and adjust values as needed.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for record numbers.
    |
    */

    'LEAVE_PREFIX' => 'LVE',

    /*
    |--------------------------------------------------------------------------
    | Tailwinds Elements Classes
    |--------------------------------------------------------------------------
    */    

    'tableClasses' => [
        'table' => 'w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400',
        'tbody' => 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200',
        'thead' => 'text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400',
        'td' => 'text-gray-600 px-6 py-3 border-t border-gray-100 whitespace-nowrap px-6 py-4',
        'th' => 'text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 px-6 py-3',
        'colTr' => 'text-left',
        'divTop' => 'mt-10 ml-10 mr-10',
        'divSeparator' => '', 
    ],    

];