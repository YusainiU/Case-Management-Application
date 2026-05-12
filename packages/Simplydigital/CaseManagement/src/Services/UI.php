<?php

namespace Simplydigital\CaseManagement\Services;

class UI
{
    public const ALERT_DEFAULT = 'border-gray-500 bg-gray-500 text-white';
    public const ALERT_IMPORTANT = 'border-red-700 bg-red-700 text-white';
    public const ALERT_INFORMATION = 'border-blue-500 bg-blue-500 text-white';
    public const ALERT_WARNING = 'border-yellow-500 bg-yellow-500 text-gray-900';
    public const DEFAULT_ICON_FILL = 'text-gray-500 border-gray-500';
    public const IMPORTANT_ICON_FILL = 'text-red-700 border-red-700';
    public const INFORMATION_ICON_FILL = 'text-blue-500 border-blue-500';
    public const WARNING_ICON_FILL = 'text-yellow-500 border-yellow-500';
    public const OPEN_CASE = 'Open';
    public const OUTSTANDING_CASE = 'Outstanding';
    public const RESOLVED_CASE = 'Resolved';
    public const CLOSED_CASE = 'closed';
    public const OUTSTANDING_CASE_FLAG = 'bg-amber-300';
    public const RESOLVED_CASE_FLAG = 'bg-blue-300';
    public const CLOSED_CASE_FLAG = 'bg-stone-300';

    public static function createArrayforMultiselect(array $options)
    {
        /*
            Convert ...
            [
                key1 => value1, 
                key2 => value2
            ] 
            to
            [
                {'key' => key1, 'value' => value1}, 
                {'key' => key2, 'value' => value2}
            ]
        */
        $result = collect($options)->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values()->toArray();
        return $result;
    }


}