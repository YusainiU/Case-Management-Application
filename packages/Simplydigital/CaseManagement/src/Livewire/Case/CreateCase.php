<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;

class CreateCase extends ModalComponent
{
    public ?int $slaId = null;    
    public ?tickets $case = null;
    public array $ui = [];
    public array $form = [];
    public string $closureEvent = 'closeCreateCaseModal';

    protected $validationAttributes = [
        'number' => 'Number',
        'customer_id' => 'Customer', 
        'sla_applications_id' => 'SLA',
        'customer_reference' => 'Customer Referennce',
        'category' => 'Case Category',
        'list_of_assets' => 'Assets',
        'description' => 'Case Description',
        'raised_by_user' => '',
        'raised_by_nonuser' => '',
        'contact_telephone' => 'Contact Telephone',
        'contact_email' => 'Contact Email',
    ];
    

}