<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\tickets;

class ManageCaseSLA extends ModalComponent
{
    public ?tickets $case = null;
    public array $forms = [];

    protected $validationAttributes = [
        'forms.responded_at' => 'Responded At',
        'forms.fixed_at' => 'Fixed At',
    ];

    protected function rules()
    {
        return [
            'forms.responded_at' => 'nullable|datetime',
            'forms.fixed_at' => 'nullable|datetime',
        ];
    }

    public function loadSLAData()
    {
        $this->forms = [
            'responded_at' => $this->case->slaTasks->responded_at ? $this->case->slaTasks->responded_at->format('Y-m-d\TH:i') : null,
            'fixed_at' => $this->case->slaTasks->fixed_at ? $this->case->slaTasks->fixed_at->format('Y-m-d\TH:i') : null,
        ];
    }

    public function render()
    {
        $this->loadSLAData();
        return view('casemanagement::livewire.case.manage-case-sla');
    }
}
