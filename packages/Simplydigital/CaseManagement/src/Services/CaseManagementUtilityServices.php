<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;

class CaseManagementUtilityServices
{
    public static function generateRecordNumber(string $prefix)
    {
        return $prefix . '-' . now()->format('YmdHis') . mt_rand(1000, 9999);
    }

    public static function formatPriceValue($value)
    {
        return '£' . number_format(\floatval($value), 2);
    }

    public static function checkIfDate($value)
    {            
        return \is_string($value) && Carbon::hasFormat($value, 'Y-m-d');    
    }

    public static function normaliseDecimalPriceValues($value)
    {
        return (float) $value;
    }

    public static function displayDateValue($value)
    {
        return Carbon::parse($value)->format('d/m/Y h:i:s');
    }

}