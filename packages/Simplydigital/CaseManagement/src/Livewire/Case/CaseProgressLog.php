<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Database\Eloquent\Collection;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
class CaseProgressLog extends ModalComponent
{

    public ?tickets $case = null;
    public ?string $logText = null;
    public bool $urgentAttention = false;
    public ?Collection $taskLogs = null;
    public ?string $message = null;

    public function logService(): CaseLogAdministrationService
    {
        return new CaseLogAdministrationService();
    }

    public function utilityService(): CaseTicketUtilityServices
    {
        return new CaseTicketUtilityServices();
    }

    public function createLog()
    {
        $util = $this->utilityService();
        $text = trim($this->logText);
        if($text) {
            $result = $util->buildProgressLog($this->case, $text);
            if($result){
                $this->message = "Added new progress log";            
                if($this->urgentAttention){
                    $util->requireAttention($result, $this->urgentAttention);
                }
            }
            $this->case->refresh(); 
        }

    }

    public function resetToastMessage()
    {
        $this->message = null;
        $this->logText = null;
    }

    public function respondLog()
    {

    }

    public function getLogs()
    {
        return $this->logService()->retrieveProgressLog($this->case);
    }

    public function render()
    {
        $this->taskLogs = $this->getLogs()->orderByDesc('created_at')->get();
        return view('casemanagement::livewire.case.case-progress-log');
    }
}