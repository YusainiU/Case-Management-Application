<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Enums\DaysOfWeek;
use Simplydigital\CaseManagement\Enums\slaTypes;


class serviceLevelAgreements extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceLevelAgreementFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'short_description',
        'sla_key',
        'start_day',
        'end_day',
        'service_start_time',
        'service_end_time',
        'include_public_holiday',
        'cover_details',
        'active',
        'type',
        'response_time',
        'fixed_time',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'include_public_holiday' => 'boolean',
            'start_day' => DaysOfWeek::class,
            'end_day' => DaysOfWeek::class,
            'type' => slaTypes::class,
        ];
    }

    /*
    Example usage:
    
    $sla = new ServiceLevelAgreement();
    $sla->start_day = DaysOfWeek::Monday;
    $sla->end_day = DaysOfWeek::Friday;
    $sla->save();
    
    $sla = ServiceLevelAgreement::find(1);
    echo $sla->start_day->label(); // Output: Monday
    echo $sla->end_day->label(); // Output: Friday  
    echo $sla->start_day->isWeekend() ? 'Weekend' : 'Weekday'; // Output: Weekday
    echo $sla->end_day->isWeekend() ? 'Weekend' : 'Weekday'; // Output: Weekday 
    echo $sla->start_day->value; // Output: 'monday'
    echo $sla->end_day->value; // Output: 'friday'
    */
  
}
