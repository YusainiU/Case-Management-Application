<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
class CloseAndResolveCase extends Component
{

    private CaseTicketServices $caseServices;
    public ?tickets $case = null;
    public string $mode = "close";
    public array $form_close  = [];
    public array $form_resolve = [];


    protected $validationAttributes = [
        'form_close.closed_datetime' => 'Case Close At',
        'form_resolve.resolved_datetime' => 'Resolved At',
    ];


    public function mount()
    {
        $this->caseServices = app(CaseTicketServices::class);        
    }

    public function close()
    {

    }

    public function resolve()
    {

    }

    public function render()
    {
        return view('casemanagement::livewire.case.close-and-resolve-case');
    }


} 