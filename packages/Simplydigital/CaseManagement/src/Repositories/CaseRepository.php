<?php

namespace Simplydigital\CaseManagement\Repositories;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\slaTask;
use App\Models\User;

class CaseRepository
{

    public function createNewTicket(array $data): tickets
    {
        return tickets::create($data);
    }

    public function createTaskLog(array $data)
    {
        return TaskLogs::create($data);
    }

    public function createSlaTask(array $data)
    {
        return slaTask::create($data);
    }

    public function updateRespondAt(slaTask $slaTask, ?string $respondedAt)
    {
        $slaTask->responded_at = $respondedAt ?? null;
        if($slaTask->isDirty('responded_at')) {            
            return $slaTask->save();
        }
        return false;
    }
    
    public function updateAssignedTo(tickets $ticket, ?user $user)
    {
        
        $ticket->assigned_to = $user ? $user->id : null;
        if($ticket->isDirty('assigned_to')) {
            return $ticket->save();
        }
        return false;
    }

    public function updateFixedAt(slaTask $slaTask, ?string $fixedAt)
    {
        $slaTask->fixed_at = $fixedAt ?? null;
        if($slaTask->isDirty('fixed_at')) {            
            return $slaTask->save();
        }
        return false;        
    }

}

