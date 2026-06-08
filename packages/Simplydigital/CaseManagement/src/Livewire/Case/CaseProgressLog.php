<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Database\Eloquent\Collection;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
class CaseProgressLog extends ModalComponent
{

    public ?tickets $case = null;
    public ?string $logText = null;
    public ?Collection $taskLogs = null;

    public function logService(): CaseLogAdministrationService
    {
        return new CaseLogAdministrationService();
    }

    public function getLogs()
    {
        return $this->logService()->retrieveProgressLog($this->case);
    }

    public function render()
    {
        $this->taskLogs = $this->getLogs()->get();
        return view('casemanagement::livewire.case.case-progress-log');
    }
}