<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Simplydigital\CaseManagement\Models\tickets;

class CaseManagementUtilityServices
{
    public static function generateRecordNumber(string $prefix)
    {
        return $prefix.'-'.now()->format('YmdHis').mt_rand(1000, 9999);
    }

    public static function generateNumber(string $prefix, string $modelName)
    {
        return CaseNumberGenerator::generateNumber($prefix, $modelName);
    }

    public static function generateCaseNumber()
    {
        $prefix = Config('case-management.TICKET_PREFIX');
        return CaseNumberGenerator::generateNumber($prefix,tickets::class);
    }

    public static function formatPriceValue(int $value)
    {
        return '£'.number_format(\floatval($value), 2);
    }

    public static function checkIfDate(?string $value)
    {
        return \is_string($value) && Carbon::hasFormat($value, 'Y-m-d');
    }

    public static function normaliseDecimalPriceValues(int $value)
    {
        return (float) $value;
    }

    public static function displayDateValue(int $value)
    {
        return Carbon::parse($value)->format('d/m/Y h:i:s');
    }

    public static function reverseYMDToDMY(string $date)
    {
        $d = Carbon::createFromFormat('Y-m-d H:i:s', $date);

        return $d->format('d/m/Y H:i:s');
    }

    public static function daysOfWeek()
    {
        $timestamp = strtotime('next Sunday');
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = strtolower(date('l', $timestamp));
            $timestamp = strtotime('+1 day', $timestamp);
        }

        return $days;
    }

    public static function isNowInsideSLA(tickets $ticket)
    {
        $sla = $ticket->slaApplication?->serviceLevelAgreement;

        if (! $sla || ! $sla->service_start_time || ! $sla->service_end_time) {
            return true;
        }

        $now = Carbon::now()->secondsSinceMidnight();

        return $now >= $sla->service_start_time
            && $now <= $sla->service_end_time;
    }

    public static function isDayInsideSla(
        string $slaStartDay,
        string $slaEndDay,
        string $dayToVerify,
    ) {
        $w = self::daysOfWeek();
        $f = array_flip($w);
        $f['sunday'] = 7;
        $start = $f[strtolower($slaStartDay)];
        $end = $f[strtolower($slaEndDay)];
        $verify = $f[strtolower($dayToVerify)];
        // Normal range (e.g. Monday-Friday)
        if ($start <= $end) {
            return $verify >= $start && $verify <= $end;
        }

        // Wrapped range (e.g. Friday-Tuesday)
        return $verify >= $start || $verify <= $end;
    }

    public static function parseTimeStampToSeconds(int $timestamp)
    {
        $r = date('H:i:s', $timestamp);
        $s = explode(':', $r);
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

    public static function secondsToHMS(int $seconds, ?string $format = null)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $hr = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $mn = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        if ($format == 'HM') {
            $result = "$hr:$mn";
        } else {
            $result = "$hr:$mn:00";
        }

        return $result;
    }

    public static function HMSToSeconds(string $time): int
    {
        $d = explode(':', $time);

        return ($d[0] * 3600) + ($d[1] * 60);
    }

    public static function convertTimeElementsToSeconds(int $hours, int $minutes)
    {
        return ($hours * 3600) + ($minutes * 60);
    }

    public static function convertStringTimeToInt(int $seconds, string $timeType)
    {
        $his = self::secondsToHMS($seconds);
        $t = explode(':', $his);
        $h = (int) $t[0];
        $m = (int) $t[1];
        $result = 0;
        if ($timeType == 'hours') {
            $result = $h;
        }
        if ($timeType == 'minutes') {
            $result = $m;
        }

        return $result;
    }

    public static function buildResponseTimeResult(int $response_time)
    {
        $h = self::convertStringTimeToInt($response_time, 'hours');
        $m = self::convertStringTimeToInt($response_time, 'minutes');
        $f = self::secondsToHM($response_time);

        return [
            'hours' => $h,
            'minutes' => $m,
            'seconds' => $response_time,
            'formatted' => $f,
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
