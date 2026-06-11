<?php

namespace Simplydigital\CaseManagement\Services;

class UI
{
    public const ALERT_DEFAULT = 'border-gray-500 bg-gray-500 text-white';
    public const ALERT_IMPORTANT = 'border-red-700 bg-red-700 text-white';
    public const ALERT_INFORMATION = 'border-blue-500 bg-blue-500 text-white';
    public const ALERT_WARNING = 'border-yellow-500 bg-yellow-500 text-white font-extrabold';
    public const DEFAULT_ICON_FILL = 'text-gray-500 border-gray-500';
    public const IMPORTANT_ICON_FILL = 'text-red-700 border-red-700';
    public const INFORMATION_ICON_FILL = 'text-blue-500 border-blue-500';
    public const WARNING_ICON_FILL = 'text-yellow-500 border-yellow-500';
    public const OPEN_STATE_CLASS = 'bg-green-300 text-green-800';
    public const CLOSED_STATE_CLASS = 'bg-gray-300 text-gray-800';
    public const RESOLVED_STATE_CLASS = 'border border-green-100 bg-green-50 text-green-900 font-bold';
    public const IN_PROGRESS_STATE_CLASS = 'border border-yellow-100 bg-yellow-50 text-yellow-900 font-bold';
    public const PAUSED_STATE_CLASS = 'border border-green-100 bg-green-50 text-green-900 font-bold';
    public const CANCELLED_STATE_CLASS = 'border border-red-100 bg-red-50 text-red-900 font-bold';
    public const COMPLETED_STATE_CLASS = 'border border-green-100 bg-green-50 text-green-900 font-bold';
    public const BREACHED_STATE_CLASS = "border border-red-100 bg-red-100 text-red-900 font-bold"; 
    public const CLOSE_TO_BREACH_CLASS = "border border-yellow-100 bg-yellow-50 text-yellow-900 font-bold";
    public const UNASSIGNED_STATE_CLASS = "border border-blue-100 bg-blue-50 text-blue-900 font-bold";
    public const NOUPDATE_STATE_CLASS = "border border-gray-100 bg-gray-50 text-gray-900 font-bold";
    public const BREACHED_STATE_TEXT = 'Breached';
    public const CRITICAL_STATE_TEXT = "Critical";
    public const WARNING_STATE_TEXT = "Warning";
    public const CAUTION_STATE_TEXT = "Caution";
    public const DEFAULT_STATE_CLASS = 'border border-gray-100 bg-gray-100 text-gray-900';

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