<?php

namespace Simplydigital\CaseManagement\Services;

class UI
{

    public const ALERT_DEFAULT = 'border-gray-200 bg-gray-100 text-gray-700 font-extrabold';
    public const ALERT_IMPORTANT = 'border-emerald-200 bg-emerald-100 text-emerald-700 font-extrabold';
    public const ALERT_INFORMATION = 'border-blue-200 bg-blue-100 text-blue-700 font-extrabold';
    public const ALERT_WARNING = 'border-yellow-200 bg-yellow-200 text-yellow-700 font-extrabold';
    public const ALERT_SUCCESS = 'border-green-200 bg-green-100 text-green-700 font-extrabold';
    public const ALERT_DANGER = 'border-red-200 bg-red-100 text-red-700 font-extrabold';

    public const DEFAULT_ICON_FILL = 'text-gray-500 border-gray-500';
    public const IMPORTANT_ICON_FILL = 'text-red-700 border-red-700';
    public const INFORMATION_ICON_FILL = 'text-blue-500 border-blue-500';
    public const WARNING_ICON_FILL = 'text-yellow-500 border-yellow-500';

    public const OPEN_STATE_CLASS = 'border border-gray-100 bg-gray-300 text-gray-800 font-bold dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100';
    public const CLOSED_STATE_CLASS = 'border border-slate-100 bg-slate-300 text-slate-800 font-bold dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100';
    public const RESOLVED_STATE_CLASS = 'border border-green-100 bg-green-50 text-green-900 font-bold dark:border-green-400/20 dark:bg-green-400/10 dark:text-green-300';
    public const IN_PROGRESS_STATE_CLASS = 'border border-blue-100 bg-blue-50 text-blue-900 font-bold dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-300';
    public const PAUSED_STATE_CLASS = 'border border-yellow-100 bg-yellow-50 text-yellow-900 font-bold dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300';
    public const CANCELLED_STATE_CLASS = 'border border-red-100 bg-red-50 text-red-900 font-bold dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-300';
    public const COMPLETED_STATE_CLASS = 'border border-green-100 bg-green-50 text-green-900 font-bold dark:border-green-400/20 dark:bg-green-400/10 dark:text-green-300';
    public const BREACHED_STATE_CLASS = 'border border-red-100 bg-red-100 text-red-900 font-bold dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-300';
    public const CLOSE_TO_BREACH_CLASS = 'border border-slate-100 bg-slate-50 text-slate-900 font-bold dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200';
    public const UNASSIGNED_STATE_CLASS = 'border border-blue-100 bg-blue-50 text-blue-900 font-bold dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-300';
    public const NOUPDATE_STATE_CLASS = 'border border-yellow-100 bg-yellow-50 text-yellow-900 font-bold dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300';
    public const DEFAULT_STATE_CLASS = 'border border-gray-100 bg-gray-100 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200';

    public const BREACHED_STATE_TEXT = 'Breached';
    public const CRITICAL_STATE_TEXT = "Critical";
    public const WARNING_STATE_TEXT = "Warning";
    public const CAUTION_STATE_TEXT = "Caution";

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
