<?php

namespace Simplydigital\CaseManagement\Repositories;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\SlaTaskPause;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Enums\CaseState;
use App\Models\User;

class CaseRepository
{

    public function createNewTicket(array $data): tickets
    {
        return tickets::create($data);
    }

    public function createPause(array $data): SlaTaskPause
    {
        return SlaTaskPause::create($data);
    }

    public function updatePause(SlaTaskPause $pause, array $data)
    {
        return $pause->update($data);
    }

    public function updateTicket(tickets $ticket, array $data)
    {
        return $ticket->update($data); 
    }

    public function createTaskLog(array $data)
    {
        return TaskLogs::create($data);
    }

    public function createSlaTask(array $data)
    {
        return slaTask::create($data);
    }

    public function updateSlaTask(slaTask $slaTask, array $data)
    {
        $slaTask->update($data);
    }

    public function updateRespondAt(?slaTask $slaTask, ?string $respondedAt)
    {
        if(!$slaTask) return false;
        $slaTask->responded_at = $respondedAt ?? null;
        if($slaTask->isDirty('responded_at')) {            
            return $slaTask->update();
        }
        return false;
    }

    public function updateAssignedTo(tickets $ticket, ?user $user)
    {
        
        $ticket->assigned_to = $user ? $user->id : null;
        if($ticket->isDirty('assigned_to')) {
            return $ticket->update();
        }
        return false;
    }

    public function updateCaseState(tickets $ticket, CaseState $caseState)
    {
        $ticket->state = $caseState;
        if($ticket->isDirty('state')) {
            return $ticket->update();
        }
        return false;
    }

    public function updateFixedAt(?slaTask $slaTask, ?string $fixedAt)
    {
        if(!$slaTask) return false;
        $slaTask->fixed_at = $fixedAt ?? null;
        if($slaTask->isDirty('fixed_at')) {            
            return $slaTask->update();
        }
        return false;        
    }

    public function updateLog(TaskLogs $taskLog, string $logText)
    {
        $taskLog->description = $logText;
        if($taskLog->isDirty('description')) {
            return $taskLog->update();
        }
        return false;
    }

    public function deleteLog(int $logId)
    {
        return TaskLogs::destroy($logId);    
    }

    public function deletePause(int $pauseId)
    {
        return SlaTaskPause::destroy($pauseId);
    }

    public function createVisit(array $data)
    {
        return TaskSiteVisit::create($data);
    }

    public function updateVisit(TaskSiteVisit $visit, array $data)
    {
        return $visit->update($data);
    }

    public function trackChangesOnVisit(TaskSiteVisit $visit, array $data)
    {
        $hasChanged = [];
        foreach($data as $column => $value){
            $visit->$column = $value;
            $hasChanged[$column] = $visit->isDirty($column);
        }
        return $hasChanged;
    }

}

