<?php

declare(strict_types=1);

/**
 * Configuration template for SimplyDigital EngineerManager package.
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
    
    'ENG_PREFIX' => 'ENG',
    'SKILL_PREFIX' => 'SKL',
    'ENGSKL_PREFIX' => 'ENGSKL',
    'CERT_PREFIX' => 'CERT',
    'ENGCERT_PREFIX' => 'ENGCERT',

    /*
    |--------------------------------------------------------------------------
    | Departments/Positions
    |--------------------------------------------------------------------------
    |
    | List of Departments & Positions
    |
    */

    'departments' => [
        'general' => 'General',
        'communications' => 'Communications',
        'network' => 'Network',
        'information' => 'Information',     
    ],

    'positions' => [
        'engineer' => 'Engineer',
        'junior_engineer' => 'Junior Engineer',
        'senior_engineer' => 'Senior Engineer',
        'lead_engineer' => 'Lead Engineer',
        'manager' => 'Manager',
        'technician' => 'Technician',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skills Levels
    |--------------------------------------------------------------------------
    |
    | List of Skill Levels
    |
    */

    'skill_levels' => [
        'apprentice' => 'Apprentice',
        'intermediate' => 'Intermediate',
        'expert' => 'Expert',
        'professional' => 'Professional', 
    ],

    /*
    |--------------------------------------------------------------------------
    | Certification Types
    |--------------------------------------------------------------------------
    |
    | List of Certification Types
    |
    */

    'certification_types' => [
        'professional' => 'Professional',
        'academic' => 'Academic',
        'training' => 'Training',    
    ],

    'certificate_issuers' => [
        'mitel' => 'Mitel Internation',
        'cisco' => 'Cisco',
        'microsoft' => 'Microsot Corporation',
        'ubuntu' => 'Linux Ubuntu',
    ],        

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
    
    'buttonClasses' => [
        'btnX' => 'text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white',
        'btnGrey' => 'bg-white text-gray-800 hover:bg-gray-100 font-semibold py-2 px-4 border border-gray-400 rounded shadow',
        'btnRed' => 'bg-red-800 text-white hover:bg-red-300 font-semibold py-2 px-4 border border-gray-400 rounded shadow',
        'btnBlue' => 'bg-blue-800 hover:bg-blue-500 text-white hover:bg-blue-300 font-semibold py-2 px-4 border border-gray-400 rounded shadow',
        'btnToggleLink' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150',
        'btnToggleContent' => 'block px-4 py-2 text-xs text-gray-400',
        'btnEdit' => "text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800",
        'btnDelete' => 'text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800',
    ],
    
    'modalClasses' => [
        'modalMain' => 'fixed inset-0 z-50 overflow-y-auto',
        'modalBackdrop' => 'fixed inset-0 w-full h-full bg-black opacity-40',
        'modalPanel' => 'relative mx-auto mt-20 bg-white rounded-md shadow-lg max-w-lg w-full',
        'modalHeaderPanel' => 'flex items-center justify-between px-6 py-4 border-b',
        'modalHeader' => 'text-lg font-medium text-gray-600',
        'modalXButton' => 'text-gray-600 hover:text-gray-800',
        'modalButtonGroup' => 'px-6 py-4 border-t text-right space-x-2',
        'modalText' => 'text-sm text-gray-600',
    ],

    'formClasses' => [
        'label' => "block uppercase tracking-wide text-xs font-bold mb-2 text-base leading-relaxed text-gray-700",
        'value' => "tracking-wide text-sm mb-2 text-base leading-relaxed text-gray-700",
        'input-normal' => "appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
        'input-required' => "appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white",
        'field-info' => "text-gray-600 text-xs italic",
        'field-warning' => "text-red-500 text-xs italic",
        'textarea' => "block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500",
        'select' => "bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500",
        'checkbox' => "w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:border-gray-600",
        'checkboxLabel' => "ms-2 text-sm font-medium text-gray-900 dark:text-gray-300",
        'error-message' => "text-red-500 text-xs italic mt-2",
        'fileInput' => 'block w-full text-xl text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400x',
    ],
    
    'listClass' => [
        'ul' => "max-w-md mx-auto bg-white shadow-lg rounded-xl divide-y divide-gray-200",
        'li' => "flex items-center gap-4 p-4 hover:bg-gray-50 transition",
        'liLeftItem' => "flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-semibold",
        'liRightTopItem' => "font-medium text-gray-900",
        'liRightBottomItem' =>"text-sm text-gray-500",
    ],

];
