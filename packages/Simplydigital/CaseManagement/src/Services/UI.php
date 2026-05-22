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
    public const OPEN_STATE_CLASS = 'bg-green-300 text-green-800';
    public const CLOSED_STATE_CLASS = 'bg-gray-300 text-gray-800';
    public const RESOLVED_STATE_CLASS = 'bg-blue-300 text-blue-800';
    public const IN_PROGRESS_STATE_CLASS = 'bg-yellow-300 text-yellow-800';
    public const PAUSED_STATE_CLASS = 'bg-orange-300 text-orange-800';
    public const CANCELLED_STATE_CLASS = 'bg-red-300 text-red-800';
    public const COMPLETED_STATE_CLASS = 'bg-green-300 text-green-800';

    public static function createArrayforMultiselect(array $options, ?string $keyName1 = 'value', ?string $keyName2 = 'label')
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



        $result = collect($options)->map(function ($label, $value) use ($keyName1, $keyName2) {
            return [
                $keyName1 => $value,
                $keyName2 => $label,
            ];
        })->values()->toArray();
        return $result;
    }


}