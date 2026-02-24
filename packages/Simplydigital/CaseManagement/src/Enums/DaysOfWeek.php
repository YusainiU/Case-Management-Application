<?php

namespace Simplydigital\CaseManagement\Enums;

enum DaysOfWeek: string
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public function label(): string
    {
        return match($this) {
            self::Monday => 'Monday',
            self::Tuesday => 'Tuesday',
            self::Wednesday => 'Wednesday',
            self::Thursday => 'Thursday',
            self::Friday => 'Friday',
            self::Saturday => 'Saturday',
            self::Sunday => 'Sunday',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($day) => [$day->value => $day->label()])
            ->toArray();
    }
    
    /*
    Example usage:
    $day = DaysOfWeek::Monday;
    echo $day->label(); // Output: Monday
    $options = DaysOfWeek::options();
    print_r($options); // Output: ['monday' => 'Monday', 'tuesday' => 'Tuesday', ...]
    
    <select name="day">
        @foreach(\Vendor\Package\Enums\DayOfWeek::options() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>    
    */

    public function isWeekend(): bool
    {
        return $this === self::Saturday || $this === self::Sunday;
    }

    /*
    Example usage:
    $day = DaysOfWeek::Saturday;
    if ($day->isWeekend()) {
        echo "It's the weekend!";
    } else {
        echo "It's a weekday."; 
    }
    */
}