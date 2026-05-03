<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;

class CasePublicHolidayService
{
    public array $publicholidaynames = [
        1 => 'New Year',
        2 => 'Good Friday',
        3 => 'Easter Monday',
        4 => 'May Day',
        5 => 'Spring Bank Holiday',
        6 => 'Summer Bank Holiday',
        7 => 'Christmas Day',
        8 => 'Boxing Day',
    ];

    public array $publicHolidays = [];
    public array $holidayDetails = [];
    public array $listByDescription = [];


    public function getPublicHolidays(string $date = null, bool $getAll = true)
    {
        // $date in dd-mm-YYYY in format

        $isdate = $date ? trim($date) : null;
        $getHolidayForSpecificDate = false;
        $tsDate = time();

        if ($isdate && preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}/', $isdate)) {
            $tsDate = strtotime($date);
            if(!$getAll){
                $getHolidayForSpecificDate = true;
            }
        }
        $thisyear = date('Y', $tsDate);
        $lastyear = $thisyear - 1;
        $nextyear = $thisyear + 1;

        $y1 = $this->getBankHolidaysForYear($thisyear);
        $y2 = $this->getBankHolidaysForYear($lastyear);
        $y3 = $this->getBankHolidaysForYear($nextyear);
        
        $ymd1 = $y1["dmy"];
        $des1 = $y1["description"];
        $ymd2 = $y2["dmy"];
        $des2 = $y2["description"];
        $ymd3 = $y3["dmy"];
        $des3 = $y3["description"];

        $holidays = [...$ymd1, ...$ymd2, ...$ymd3];
        $descriptions = [...$des1, ...$des2, ...$des3];

        if ($getHolidayForSpecificDate && \in_array($isdate, $holidays)) {
            $s1 = $holidays[$isdate];
            $s2 = $descriptions[$isdate];
            //Reset Holidays and description
            //re-initialise with new arrays of holidays for specific date                         
            $holidays = $descriptions = [];
            $holidays[$isdate] = $s1;
            $descriptions[$isdate] = $s2;     
        }

        $this->publicHolidays = $holidays;
        $this->holidayDetails = $descriptions;

        return $holidays;

    }

    public function getBankHolidaysForYear(int $year)
    {

        $bankHols = [];
        switch (date("w", strtotime("$year-01-01 12:00:00"))) {
            case 6:
                $bankHols[] = $ddate = "$year-01-03";
                break;
            case 0:
                $bankHols[] = $ddate = "$year-01-02";
                break;
            default:
                $bankHols[] = $ddate = "$year-01-01";
        }
        $des[$ddate]['New Year'] = 1;

        // Good friday:
        $bankHols[] = $ddate = date("Y-m-d", strtotime("+" . (easter_days($year) - 2) . " days", strtotime("$year-03-21 12:00:00")));
        $des[$ddate]['Good Friday'] = 2;

        // Easter Monday:
        $bankHols[] = $ddate = date("Y-m-d", strtotime("+" . (easter_days($year) + 1) . " days", strtotime("$year-03-21 12:00:00")));
        $des[$ddate]['Easter Monday'] = 3;

        // May Day:
        switch (date("w", strtotime("$year-05-01 12:00:00"))) {
            case 0:
                $bankHols[] = $ddate = "$year-05-02";
                break;
            case 1:
                $bankHols[] = $ddate = "$year-05-01";
                break;
            case 2:
                $bankHols[] = $ddate = "$year-05-07";
                break;
            case 3:
                $bankHols[] = $ddate = "$year-05-06";
                break;
            case 4:
                $bankHols[] = $ddate = "$year-05-05";
                break;
            case 5:
                ## replacing year-05-04
                $bankHols[] = $ddate = "$year-05-08";
                break;
            case 6:
                $bankHols[] = $ddate = "$year-05-03";
                break;
        }
        $des[$ddate]['May Day'] = 4;

        // Whitsun:
        switch (date("w", strtotime("$year-05-31 12:00:00"))) {
            case 0:
                $bankHols[] = $ddate = "$year-05-25";
                break;
            case 1:
                $bankHols[] = $ddate = "$year-05-31";
                break;
            case 2:
                $bankHols[] = $ddate = "$year-05-30";
                break;
            case 3:
                $bankHols[] = $ddate = "$year-05-29";
                break;
            case 4:
                $bankHols[] = $ddate = "$year-05-28";
                break;
            case 5:
                $bankHols[] = $ddate = "$year-05-27";
                break;
            case 6:
                $bankHols[] = $ddate = "$year-05-26";
                break;
        }
        $des[$ddate]["Spring Bank Holiday"] = 5;

        // Summer Bank Holiday:
        switch (date("w", strtotime("$year-08-31 12:00:00"))) {
            case 0:
                $bankHols[] = $ddate = "$year-08-25";
                break;
            case 1:
                $bankHols[] = $ddate = "$year-08-31";
                break;
            case 2:
                $bankHols[] = $ddate = "$year-08-30";
                break;
            case 3:
                $bankHols[] = $ddate = "$year-08-29";
                break;
            case 4:
                $bankHols[] = $ddate = "$year-08-28";
                break;
            case 5:
                $bankHols[] = $ddate = "$year-08-27";
                break;
            case 6:
                $bankHols[] = $ddate = "$year-08-26";
                break;
        }
        $des[$ddate]["Summer Bank Holiday"] = 6;

        // Christmas:
        switch (date("w", strtotime("$year-12-25 12:00:00"))) {
            case 5:
                $bankHols[] = $ddate = "$year-12-25";
                $bankHols[] = $bdate = "$year-12-28";
                break;
            case 6:
                $bankHols[] = $ddate = "$year-12-27";
                $bankHols[] = $bdate = "$year-12-28";
                break;
            case 0:
                $bankHols[] = $ddate = "$year-12-26";
                $bankHols[] = $bdate = "$year-12-27";
                break;
            default:
                $bankHols[] = $ddate = "$year-12-25";
                $bankHols[] = $bdate = "$year-12-26";
        }
        $des[$ddate]["Christmas Day"] = 7;
        $des[$bdate]["Boxing Day"] = 8;

        foreach ($bankHols as $v) {
            list($y, $m, $d) = explode("-", $v);
            $dmy[] = "$d-$m-$y";
        }

        foreach ($des as $k => $v) {
            list($y, $m, $d) = explode("-", $k);
            $edate = "$d-$m-$y";
            $description[$edate] = key($v);
            $this->listByDescription[key($v)][$year][] = $edate;
        }

        $result["ymd"] = $bankHols;
        $result["dmy"] = $dmy;
        $result["description"] = $description;

        return $result;

    }  
    
    
}