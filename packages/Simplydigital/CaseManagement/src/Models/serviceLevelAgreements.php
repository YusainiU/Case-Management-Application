<?php

namespace Simplydigital\CaseManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Enums\DaysOfWeek;
use Simplydigital\CaseManagement\Enums\slaTypes;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Illuminate\Database\Eloquent\Casts\Attribute;

class serviceLevelAgreements extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'name',
        'short_description',
        'number',
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

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.SLA_PREFIX');
            $utilityService = app('case-management-utility-services');
            $model->number = $utilityService::generateRecordNumber($prefix);
        });
    }

    protected function responseTime(): Attribute
    {
        return Attribute::make(
            // When saving to DB
            set: fn ($value) => $value * 3600,
        );
    }
    
    public function fixedTime(): Attribute
    {
        return Attribute::make(
            // When saving to DB
            set: fn ($value) => $value * 3600,
        );
    }

    public function utilityService(): CaseManagementUtilityServices
    {
        return app(CaseManagementUtilityServices::class);
    }

    public function getServiceStartTime(): string
    {
        $start = $this->service_start_time;
        if($start){
            return app(CaseManagementUtilityServices::class)->secondsToHMS($start);
        }
        return '';
    }

    public function getServiceEndTime(): string
    {
        $end = $this->service_end_time;
        if($end){
            return app(CaseManagementUtilityServices::class)->secondsToHMS($end);
        }
        return '';
    }    
        
    public function getResponseTime()
    {
        $rpt = $this->response_time;
        if($rpt){
            return app(CaseManagementUtilityServices::class)->buildResponseTimeResult($rpt)['hours'];       
        };
        return null;        
    }

    public function getFixedTime()
    {
        $rpx = $this->fixed_time;
        if($rpx) {
            return app(CaseManagementUtilityServices::class)->buildResponseTimeResult($rpx)['hours'];
        };
        return null;
    }

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
    Example usage with DaysOfWeek enum:
    
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
