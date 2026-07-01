<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\tickets;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Simplydigital\CaseManagement\Repositories\CaseRepository;

class CaseLogAdministrationService
{

    private ?CaseRepository $caseRepository;

    public function __construct(?CaseRepository $caseRepository = null)
    {
        $this->caseRepository = $caseRepository ?? app()->make(CaseRepository::class);
    }

    public function createSystemLog(tickets $ticket, string $logText, ?string $logType = null)
    {
        $ext = $ticket->raised_by_nonuser;
        $source = $ext ? config('case-management.logs.source.external') : $source = config('case-management.logs.source.internal');
        $type = $logType ?? config('case-management.logs.type.system');

        $currentUserId =  Auth::id(); //auth()->id();
        $currentUserName = Auth::getName(); //auth()->user()->name;
        $name = $ticket->number;
        $ticket_id = $ticket->id;

        $data = [
            'number' => null,
            'name' => $name,
            'short_description' => $name,
            'type' => $type,
            'source' => $source,
            'user_id' => $currentUserId,
            'ticket_id' => $ticket_id,
            'description' => $logText,
        ];
        
        if ($type == config('case-management.logs.type.sla_tasks')) {
            $sla = $ticket->slaTasks()->first();
            $data['sla_tasks_id'] = $sla?->id;
        }
        
        //$log = taskLogs::create($data);
        $log = $this->caseRepository->createTaskLog($data);
        return $log;        
    }

    public function buildVisitUpdatedScheduledLog(string $newSchedule, string $oldSchedule, string $user)
    {
        $phrase = config('case-management.siteVisit.scheduledUpdated') ?? '';
        $searchFor = ['%SCHEDULED%', '%DATETIME%', '%USER%'];
        $replaceWith = [$newSchedule, $oldSchedule, $user];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildVisitUpdatedAssignedTo(string $oldAssignee, string $newAssignee, string $user)
    {
        $phrase = config('case-management.siteVisit.assignedToUpdated') ?? '';
        $searchFor = ['%CURRENT%', '%NEW%', '%USER%'];
        $replaceWith = [$oldAssignee, $newAssignee, $user];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildVisitUpdatedEnroute(array $data)
    {
        $searchFor = [];
        $phrase = '';
        if ($data['action'] == 'update') {
            $phrase = config('case-management.siteVisit.enrouteUpdated') ?? '';
            $searchFor = ['%ASSIGNEE%', '%DATETIME%', '%USER%'];
            $replaceWith = [$data['assignee'], $data['datetime'], $data['user']];
        }
        if($data['action'] == 'remove'){
            $phrase = config('case-management.siteVisit.enrouteRemoved') ?? '';
            $searchFor = '%USER%';
            $replaceWith = $data['user'];
        }
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildVisitUpdatedOnsite(array $data)
    {
        $searchFor = [];
        $phrase = '';
        if ($data['action'] == 'update') {
            $phrase = config('case-management.siteVisit.onsiteUpdate') ?? '';
            $searchFor = ['%ASSIGNEE%', '%DATETIME%', '%USER%'];
            $replaceWith = [$data['assignee'], $data['datetime'], $data['user']];
        }
        if($data['action'] == 'remove'){
            $phrase = config('case-management.siteVisit.onsiteRemoved') ?? '';
            $searchFor = '%USER%';
            $replaceWith = $data['user'];
        }
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;        
    }

    public function buildVisitUpdatedOffsite(array $data)
    {
        $searchFor = [];
        $phrase = '';
        if ($data['action'] == 'update') {
            $phrase = config('case-management.siteVisit.offsiteUpdate') ?? '';
            $searchFor = ['%ASSIGNEE%', '%DATETIME%', '%USER%'];
            $replaceWith = [$data['assignee'], $data['datetime'], $data['user']];
        }
        if($data['action'] == 'remove'){
            $phrase = config('case-management.siteVisit.offsiteRemoved') ?? '';
            $searchFor = '%USER%';
            $replaceWith = $data['user'];
        }
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildVisitLog(string $assignee, string $scheduler, string $datetime)
    {
        /*
            assignee -- person to visit
            scheduler -- person who arranged the visit
        */
        $phrase = config('case-management.siteVisit.visitCreated') ?? '';
        $searchFor = ['%ASSIGNEE%', '%SCHEDULER%', '%DATETIME%'];
        $replaceWith = [$assignee, $scheduler, $datetime];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildVisitNote(string $createdBy, string $visitNote)
    {
        $phrase = config('case-management.siteVisit.VisitNote') ?? '';
        $searchFor = ['%CREATEDBY%', '%NOTE%'];
        $replaceWith = [$createdBy, $visitNote];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildVisitDeletion(string $shortDescription, string $user)
    {
        $now = date('d-m-Y H:i', time());
        $phrase = config('case-management.siteVisit.delete') ?? '';
        $searchFor = ['%DESCRIPTION%','%DATETIME%', '%USER%'];
        $replaceWith = [$shortDescription, $now, $user];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;        
    }

    public function buildResolveText(string $user, string $datetime)
    {
        $phrase = config('case-management.ProgressLogs.resolve') ?? '';
        $searchFor = ['%USER%', '%DATETIME%'];
        $replaceWith = [$user, $datetime];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildCompletedText(string $dateCompleted)
    {
        $user = Auth::user()->name;
        $phrase = config('case-management.ProgressLogs.complete') ?? '';
        $searchFor = '%USER%';
        $replaceWith = $user;
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildAssignedToText(tickets $ticket, ?User $assignedTo)
    {
        if(!$assignedTo){
            return $this->buildUnassignedText($ticket);
        }        
        $user = Auth::user()->name;
        $now = date('d-m-Y H:i', time());
        $phrase = config('case-management.ProgressLogs.assignedTo') ?? '';
        $searchFor = ['%USER%', '%ASSIGNEE%', '%DATETIME%'];
        $replaceWith = [$user, $assignedTo->name, $now];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildUnassignedText(tickets $ticket)
    {
        $user = Auth::user()->name;
        $now = date('d-m-Y H:i', time());
        $assignee = $ticket->assignedToUser?->name ?? 'case';
        $phrase = config('case-management.ProgressLogs.unassigned') ?? '';
        $searchFor = ['%USER%', '%ASSIGNEE%', '%DATETIME%'];
        $replaceWith = [$user, $assignee, $now];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildProgressEmailLog(taskLogs $taskLog, string $receiver)
    {
        $phrase = config('case-management.ProgressLogs.email') ?? '';
        $searchFor = ['%LOGNUMBER%', '%RECEIVER%'];
        $replaceWith = [$taskLog->number, $receiver];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildCurrentWorkingText(tickets $ticket)
    {
        $user = Auth::user()->name;
        if ($ticket->currently_working) {
            $phrase = config('case-management.ProgressLogs.working') ?? '';
        } else {
            $phrase = config('case-management.ProgressLogs.unsetWorking') ?? '';
        }
        $searchFor = '%USER%';
        $replaceWith = $user;
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildTicketCreatedText(tickets $ticket)
    {
        $createdByName = $ticket->raised_by_nonuser;
        $created = date('d-m-Y H:i', strtotime($ticket->created_at));
        if (!$createdByName) {
            $createdByName = $ticket->raisedByUser->customerUser->name ?? 'Unknown User';
        }
        $phrase = config('case-management.ticket.create') ?? '';
        $searchFor = ['%CREATED%', '%BY%'];
        $replaceWith = [$created, $createdByName];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildSlaTaskCreatedText(slaTask $slaTask)
    {
        $created = date('d-m-Y H:i', strtotime($slaTask->created_at));
        $phrase = config('case-management.slaTask.create') ?? '';
        $searchFor = '%CREATED%';
        $replaceWith = $created;
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildProgressLogText(string $logText, string $logInfo, string $logExtraInfo)
    {
        $phrase = config('case-management.ProgressLogs.create') ?? '';
        $searchFor = ['%INFO%', '%NOTE%', '%EXTRAINFO%'];
        $replaceWith = [$logInfo, $logText, $logExtraInfo];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildRespondAtLogText(?string $responseDateTime, string $respondedBy)
    {
        $responseDateTime ?? 'has been reset'; 
        $phrase = config('case-management.ProgressLogs.respondAt') ?? '';
        $searchFor = ['%RESPONSE%', '%BY%'];
        $replaceWith = [$responseDateTime, $respondedBy];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildFixedAtLogText(?string $fixedAtDateTime, string $fixedBy)
    {
        $fixedDateTime ?? 'has been reset';
        $phrase = config('case-management.ProgressLogs.fixedAt') ?? '';
        $searchFor = ['%FIX%', '%BY%'];
        $replaceWith = [$fixedAtDateTime, $fixedBy];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }
    
    public function buildBreachLogText(string $breachDateTime)
    {
        $phrase = config('case-management.ProgressLogs.breach') ?? '';
        $searchFor = '%BREACH%';
        $replaceWith = $breachDateTime;
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildRemoveRequireAttentionText(string $removeFrom)
    {
        $phrase = config('case-management.ProgressLogs.removeRequireAttention') ?? '';
        $user = Auth::user()->name;
        $searchFor = ['%USER%', '%FROM%'];
        $replaceWith = [$user, $removeFrom];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildResponseToCommentText(string $response)
    {
        $phrase = config('case-management.ProgressLogs.respondToComment') ?? '';
        $user = Auth::user()->name;
        $searchFor = ['%USER%','%RESPONSE%'];
        $replaceWith = [$user, $response];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;
    }

    public function buildPauseText(string $pauseDateTime)
    {
        $phrase = config('case-management.ProgressLogs.pause') ?? '';
        $user = Auth::user()->name;
        $searchFor = ['%USER%','%DATETIME%'];
        $replaceWith = [$user, $pauseDateTime];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;              
    }

    public function buildChangeRespondByText(string $from, string $to)
    {
        $phrase = config('case-management.ProgressLogs.respondByTimeChanged') ?? '';
        $searchFor = ['%FROM%','%TO%'];
        $replaceWith = [$from, $to];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;        
    }

    public function buildResumeText(string $resumeDateTime)
    {
        $phrase = config('case-management.ProgressLogs.resume') ?? '';
        $user = Auth::user()->name;
        $searchFor = ['%USER%','%DATETIME%'];
        $replaceWith = [$user, $resumeDateTime];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;              
    }

    public function buildCancelPauseText(string $cancelPauseDateTime)
    {
        $phrase = config('case-management.ProgressLogs.cancelPause') ?? '';
        $user = Auth::user()->name;
        $searchFor = ['%USER%','%DATETIME%'];
        $replaceWith = [$user, $cancelPauseDateTime];
        $logString = str_replace($searchFor, $replaceWith, $phrase);
        return $logString;              
    }

    public function retrieveProgressLog(?tickets $ticket)
    {
        if(!$ticket) return false;
        return $ticket->taskLogs();
    }

    
}