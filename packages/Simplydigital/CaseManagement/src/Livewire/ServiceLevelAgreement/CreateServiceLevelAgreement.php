<?php

namespace Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;

class CreateServiceLevelAgreement extends ModalComponent
{

    public ?int $slaId = null;
    
    public serviceLevelAgreements $sla;

    public array $ui = [];

    public array $form = [];

    public string $closureEvent = 'closeCreateAssetModal';

    protected $rules = [
        'form.name' => 'required',
        'form.short_description' => 'required|string|max:255',
        'form.start_day' => 'required|string|max:30',
        'form.end_day' => 'required|string|max:30',
        'form.service_start_time' => 'required|int',
        'form.service_end_time' => 'required|int',
        'form.include_public_holiday' => 'boolean',
        'form.cover_details' => 'required|string|max:255',
        'form.active' => 'boolean',
        'form.response_time' => 'required|int',
        'form.fixed_time' => 'required|int',
    ];
    
    protected $validationAttributes = [
        'form.name' => 'Name',
        'form.short_description' => 'Short Description',
        'form.start_day' => 'Start Day',
        'form.end_day' => 'End Day',
        'form.service_start_time' => 'Start Time',
        'form.service_end_time' => 'End Time',
        'form.include_public_holiday' => 'Include Public Holiday',
        'form.cover_details' => 'Cover Details',
        'form.active' => 'Active',
        'form.response_time' => 'Response Time',
        'form.fixed_time' => 'Fixed Time',
    ];         
}