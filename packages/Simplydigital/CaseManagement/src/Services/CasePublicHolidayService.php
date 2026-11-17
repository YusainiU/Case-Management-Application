<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

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

    private ?string $loadedCalendar = null;

    /**
     * @return list<string>
     */
    public function getPublicHolidays(?string $date = null, bool $getAll = true): array
    {
        $day = $date === null
            ? Carbon::now(config('app.timezone', 'UTC'))
            : Carbon::createFromFormat('!d-m-Y', trim($date));
        if ($date !== null && $day->format('d-m-Y') !== trim($date)) {
            throw new InvalidArgumentException('Public holiday date must be a valid dd-mm-YYYY date.');
        }

        $this->listByDescription = [];
        $holidays = [];
        $descriptions = [];
        $year = $day->year;
        foreach ($getAll ? [$year, $year - 1, $year + 1] : [$year] as $calendarYear) {
            $result = $this->getBankHolidaysForYear($calendarYear);
            $holidays = [...$holidays, ...$result['dmy']];
            $descriptions = array_replace($descriptions, $result['description']);
        }

        if (! $getAll) {
            $dateString = $day->format('d-m-Y');
            $holidays = in_array($dateString, $holidays, true) ? [$dateString] : [];
            $descriptions = array_intersect_key($descriptions, array_flip($holidays));
        }

        $this->publicHolidays = $holidays;
        $this->holidayDetails = $descriptions;

        return $holidays;
    }

    public function getCountryCode(): string
    {
        $country = strtoupper(trim((string) config('case-management.public_holidays.country', 'GB')));

        if (! preg_match('/^[A-Z]{2}$/D', $country)) {
            throw new InvalidArgumentException(
                'Public holiday country must be an ISO 3166-1 alpha-2 code, such as GB, FR or MY.'
            );
        }

        return $country;
    }

    /**
     * Includes all calendar settings so long-lived SLA services cannot reuse
     * another country, subdivision or custom calendar's dates.
     */
    public function getCalendarKey(): string
    {
        return hash('sha256', json_encode([
            $this->getCountryCode(),
            $this->getSource(),
            $this->getSubdivision(),
            config('case-management.public_holidays.custom', []),
        ], JSON_THROW_ON_ERROR));
    }

    private function getSubdivision(): ?string
    {
        $subdivision = strtoupper(trim((string) config('case-management.public_holidays.subdivision', '')));
        if ($subdivision === '') {
            return null;
        }

        if (! preg_match('/^'.$this->getCountryCode().'-[A-Z0-9]{1,3}$/D', $subdivision)) {
            throw new InvalidArgumentException('Public holiday subdivision must be an ISO 3166-2 code belonging to the selected country.');
        }

        return $subdivision;
    }

    private function getSource(): string
    {
        $source = strtolower(trim((string) config('case-management.public_holidays.source', 'auto')));
        if (! in_array($source, ['auto', 'nager', 'custom'], true)) {
            throw new InvalidArgumentException('Public holiday source must be auto, nager or custom.');
        }

        return $source === 'auto'
            ? ($this->getCountryCode() === 'GB' && $this->getSubdivision() === null ? 'legacy' : 'nager')
            : $source;
    }

    /**
     * @return array{ymd: list<string>, dmy: list<string>, description: array<string, string>}
     */
    public function getBankHolidaysForYear(int $year): array
    {
        $calendar = $this->getCalendarKey();
        if ($this->loadedCalendar !== $calendar) {
            $this->publicHolidays = [];
            $this->holidayDetails = [];
            $this->publicholidaynames = [];
            $this->listByDescription = [];
            $this->loadedCalendar = $calendar;
        }

        $source = $this->getSource();
        $result = match ($source) {
            'legacy' => $this->getEnglandWalesHolidaysForYear($year),
            'custom' => $this->formatHolidays($this->getCustomHolidays($year)),
            default => Cache::remember(
                'case-public-holidays:v1:'.$calendar.':'.$year,
                86400,
                fn (): array => $this->formatHolidays($this->getRemoteHolidays($year))
            ),
        };

        $names = array_values(array_unique($result['description']));
        $this->publicholidaynames = $names === [] ? [] : array_combine(range(1, count($names)), $names);
        foreach ($result['description'] as $date => $name) {
            $this->listByDescription[$name][$year] ??= [];
            if (! in_array($date, $this->listByDescription[$name][$year], true)) {
                $this->listByDescription[$name][$year][] = $date;
            }
        }

        return $result;
    }

    /**
     * A full, explicitly configured annual calendar supports any country,
     * including countries not covered by the remote provider.
     *
     * @return array<string, string>
     */
    private function getCustomHolidays(int $year): array
    {
        $country = $this->getCountryCode();
        $years = config('case-management.public_holidays.custom.'.$country, []);
        if (! is_array($years) || ! array_key_exists($year, $years) || ! is_array($years[$year])) {
            throw new RuntimeException("No custom public holiday calendar configured for $country/$year.");
        }

        foreach ($years[$year] as $date => $name) {
            if (! $this->isValidHolidayDate($date, $year) || ! is_string($name) || trim($name) === '') {
                throw new InvalidArgumentException("Invalid custom public holiday for $country/$year. Use YYYY-MM-DD => name.");
            }
        }

        return $years[$year];
    }

    private function isValidHolidayDate(mixed $date, int $year): bool
    {
        if (! is_string($date) || ! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)) {
            return false;
        }

        [$dateYear, $month, $day] = array_map('intval', explode('-', $date));

        return $dateYear === $year && checkdate($month, $day, $dateYear);
    }

    /** @return array<string, string> */
    private function getRemoteHolidays(int $year): array
    {
        $country = $this->getCountryCode();
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
                ->get("https://nagerholidays.com/api/v4/Holidays/$country/$year")
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new RuntimeException(
                "Unable to load public holidays for $country/$year. Retry or configure a custom calendar.",
                0,
                $exception
            );
        }

        $rows = $response->json();
        if (! is_array($rows) || ! array_is_list($rows) || $rows === []) {
            throw new RuntimeException("No valid public holiday data for $country/$year. Configure a custom calendar.");
        }

        $dates = [];
        $subdivision = $this->getSubdivision();
        foreach ($rows as $row) {
            if (! is_array($row)
                || ! $this->isValidHolidayDate($row['date'] ?? null, $year)
                || ($row['countryCode'] ?? null) !== $country
                || ! is_string($row['name'] ?? null) || trim($row['name']) === ''
                || ! is_bool($row['nationalHoliday'] ?? null)
                || ! is_array($row['holidayTypes'] ?? null)
                || (isset($row['subdivisionCodes']) && ! is_array($row['subdivisionCodes']))) {
                throw new RuntimeException("Invalid public holiday data for $country/$year.");
            }

            $applies = $row['nationalHoliday']
                || ($subdivision !== null && in_array($subdivision, $row['subdivisionCodes'] ?? [], true));
            if ($applies && in_array('Public', $row['holidayTypes'], true)) {
                $date = $row['date'];
                $dates[$date] = isset($dates[$date]) ? $dates[$date].' / '.$row['name'] : $row['name'];
            }
        }

        return $dates;
    }

    /**
     * @param  array<string, string>  $dates
     * @return array{ymd: list<string>, dmy: list<string>, description: array<string, string>}
     */
    private function formatHolidays(array $dates): array
    {
        ksort($dates);
        $description = [];
        foreach ($dates as $date => $name) {
            [$year, $month, $day] = explode('-', $date);
            $description["$day-$month-$year"] = $name;
        }

        return [
            'ymd' => array_keys($dates),
            'dmy' => array_keys($description),
            'description' => $description,
        ];
    }

    private function getEnglandWalesHolidaysForYear(int $year): array
    {

        $bankHols = [];
        $dmy=[];
        $des = [];
        $description = [];
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

        // England/Wales-style May bank holidays:
        // early May = first Monday, spring = last Monday of May,
        // summer = last Monday of August. The previous weekday
        // switch could return Wednesdays/Fridays instead of Mondays.
        $mayDay = Carbon::create($year, 5, 1, 12, 0, 0);
        if (! $mayDay->isMonday()) {
            $mayDay->next(Carbon::MONDAY);
        }
        $bankHols[] = $ddate = $mayDay->format('Y-m-d');
        $des[$ddate]['May Day'] = 4;

        $spring = Carbon::create($year, 5, 31, 12, 0, 0);
        if (! $spring->isMonday()) {
            $spring->previous(Carbon::MONDAY);
        }
        $bankHols[] = $ddate = $spring->format('Y-m-d');
        $des[$ddate]['Spring Bank Holiday'] = 5;

        $summer = Carbon::create($year, 8, 31, 12, 0, 0);
        if (! $summer->isMonday()) {
            $summer->previous(Carbon::MONDAY);
        }
        $bankHols[] = $ddate = $summer->format('Y-m-d');
        $des[$ddate]['Summer Bank Holiday'] = 6;

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
        }

        $result["ymd"] = $bankHols;
        $result["dmy"] = $dmy;
        $result["description"] = $description;

        return $result;

    }  
    
    
}