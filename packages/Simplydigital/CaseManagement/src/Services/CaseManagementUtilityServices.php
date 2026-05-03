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

    public static function daysOfWeek()
    {
        $timestamp = strtotime('next Sunday');
        $days = array();
        for ($i = 0; $i < 7; $i++) {
            $days[] = strtolower(date('l', $timestamp));
            $timestamp = strtotime('+1 day', $timestamp);
        }
        return $days;
    }
    
    public static function isDayInsideSla(
        int $slaStartDate, 
        string $slaStartDay, 
        string $slaEndDay, 
        string $dayToVerify, 
        string $startDay
    )
    {
        $w = self::daysOfWeek();
        $f = array_flip($w);
        $f['sunday'] = 7;
        foreach ($w as $key => $day) {
            if ($day == strtolower($startDay)) {
                $start = $f[strtolower($slaStartDay)];
                $end = $f[strtolower($slaEndDay)];
                $v = $f[strtolower($dayToVerify)];
                if ($v >= $start && $end >= $v) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function parseTimeStampToSeconds(int $timestamp)
    {
        $r = date('H:i:s', $timestamp);
        $s = explode(":", $r);
        $h = (int) $s[0];
        $m = (int) $s[1];
        $i = (int) $s[2];
        $result = ($h * 3600) + ($m * 60) + $i;
        return $result;
    }
    
    public static function parseSeconds(int $seconds)
    {

        $days = \intval($seconds / 86400);
        $remain = $seconds % 86400;
        $hours = \intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = \intval($remain / 60);
        $secs = $remain % 60;

        return [
            'days' => $days,
            'hours' => $hours,
            'mins' => $mins,
            'seconds' => $secs,
        ];

    }
    
    public static function secondsToHM(int $seconds)
    {

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return [
            'hours' => $hours,
            'minutes' => $minutes,
        ];
    }
    
    public static function timeDiff(int $start, int $end)
    {
        $timediff = $start - $end;
        $result = self::parseSeconds($timediff);
        return $result;
    }
    
    public static function timeFromCreate(string $created_at)
    {
        $startDate = $created_at;
        $dur = round((time() - strtotime($startDate)) / 3600, 1);
        return $dur;
    }    

}