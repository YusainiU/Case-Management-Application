<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Database\Eloquent\Collection;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
use Simplydigital\CaseManagement\Repositories\CaseRepository;    
class CaseProgressLog extends ModalComponent
{

    public ?tickets $case = null;
    public ?string $logText = null;
    public bool $urgentAttention = false;
    public ?Collection $taskLogs = null;
    public ?string $message = null;
    public bool $respondToComment = false;
    public ?TaskLogs $respondToLog = null; 
    public ?int $logToDelete = null;
    public ?TaskLogs $logToEdit = null;
    public bool $notifyCustomer = false;
    public bool $makeVisibleToCustomer = false;

    public function logService(): CaseLogAdministrationService
    {
        return new CaseLogAdministrationService();
    }

    public function utilityService(): CaseTicketUtilityServices
    {
        return new CaseTicketUtilityServices();
    }

    public function repoUtility(): CaseRepository
    {
        return new CaseRepository();
    }

    public function mount()
    {
        $this->authorize('viewAny', tickets::class);
    }

    public function createLog()
    {

        $this->authorize('update', $this->case);
        $util = $this->utilityService();
        $text = trim($this->logText);
        if($text) {
            $result = $util->buildProgressLog($this->case, $text, $this->notifyCustomer);
            if($result){
                $this->message = "Added new progress log";            
                if($this->urgentAttention){
                    $util->requireAttention($result, $this->urgentAttention);
                }
                $result->visible_to_external = $this->makeVisibleToCustomer;
                $result->update();
            }
            $this->case->refresh();
            $this->dispatch('reloadCaseStatus'); 
        }

    }

    public function deleteLog(int|string $logId)
    {
        $this->authorize('update', $this->case);
        $logId = (int) $logId;
        $this->logToDelete = $logId;
    }

    public function deleteThisLog()
    {
        $this->authorize('update', $this->case);
        if($this->logToDelete) {
            $this->repoUtility()->deleteLog($this->logToDelete);
        }
        $this->case->refresh();
        $this->closeDeleteModal();        
    }

    public function editLog(int|string $logId)
    {
        $this->authorize('update', $this->case);
        $logId = (int) $logId;
        $this->logToEdit = $this->taskLogs->find($logId);
        $this->logText = $this->logToEdit->description;
        $this->dispatch('modal-scroll-top');
    }

    public function editThisLog()
    {
        $this->authorize('update', $this->case);
        if($this->logToEdit && trim($this->logText)) {
            $logText = trim($this->logText);
            $edited = $this->repoUtility()->updateLog($this->logToEdit, $logText);
            if($this->urgentAttention) {
                $util = $this->utilityService();
                $util->requireAttention($this->logToEdit, $this->urgentAttention);
            }
            if($this->notifyCustomer){
                $this->utilityService()->emailLog($this->logToEdit);
            }
            $this->logToEdit->visible_to_external = $this->makeVisibleToCustomer;
            if($this->logToEdit->isDirty('visible_to_external')) {
                $this->logToEdit->update();
            }    
        }
        $this->case->refresh();
        $this->cancelRespond();
    }

    public function closeDeleteModal()
    {
        $this->cancelRespond();
    }

    public function resetToastMessage()
    {
        $this->cancelRespond();
    }

    public function respondLog(int|string $logId)
    {
        $logId = (int) $logId;
        $this->respondToLog = $this->taskLogs->find($logId);
        $this->respondToComment = true;
        $this->dispatch('modal-scroll-top');
    }

    public function createRespond(int|string $logId)
    {
        $this->authorize('update', $this->case);
        if(!trim($this->logText)) return false;
        $logId = (int) $logId;
        $util = $this->utilityService();
        $respond = $util->respondToComment($logId, $this->logText);
        if($respond && $this->urgentAttention) {
            $util->requireAttention($respond, $this->urgentAttention);
        }
        if($this->notifyCustomer) {
            $util->emailLog($respond);
        }
        $respond->visible_to_external = $this->makeVisibleToCustomer;
        $respond->update();
        $this->cancelRespond();
        $this->case->refresh();
    }

    public function cancelRespond()
    {
        $this->reset([
            'respondToLog',
            'respondToComment',
            'logText',
            'urgentAttention',
            'notifyCustomer',
            'message',
            'logToDelete',
            'logToEdit',
        ]);
    }

    public function removeRequireAttention(int|string $logId)
    {
        $this->authorize('update', $this->case);
        $logId = (int) $logId;
        $this->utilityService()->removeRequireAttention($logId);
        $this->case->refresh();
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