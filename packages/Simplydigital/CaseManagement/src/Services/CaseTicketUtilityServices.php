<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Mail\DefaultEmail;
use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\TaskLogs;    
use Simplydigital\CaseManagement\Notifications\DefaultNotification;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Simplydigital\CaseManagement\Services\CaseRetrieveTicketStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class CaseTicketUtilityServices
{

    private CasePublicHolidayService $casePublicHolidayService;
    private CaseLogAdministrationService $caseLogAdministrationService;
    private SLACalculationService $calcService;    
    protected CaseRepository $caseRepository;
    protected CaseRetrieveTicketStatus $caseRetrieveTicketStatus;

    public function __construct(?CaseRepository $caseRepository = null)
    {
        $this->casePublicHolidayService = app()->make(CasePublicHolidayService::class);
        $this->caseRepository = $caseRepository ?? app()->make(CaseRepository::class);
        $this->caseRetrieveTicketStatus = app()->make(CaseRetrieveTicketStatus::class);
        $this->setCaseLogAdministrationService(app()->make(CaseLogAdministrationService::class));
        $this->setSLACalculationService(app()->make(SLACalculationService::class));   
    }

    public function setCaseLogAdministrationService(CaseLogAdministrationService $service)
    {
        $this->caseLogAdministrationService = $service;
    }

    public function setSLACalculationService(SLACalculationService $service)
    {
        $this->calcService = $service;
    }

    public function getRespondByDateTime(tickets $ticket)
    {
        $data = $this->buildCalcData($ticket, 'response_time');
        $respondBy = $this->calcService->calcRespondByTime($data);
        return $respondBy;
    }

    public function getFixByDateTime(tickets $ticket)
    {
        $data = $this->buildCalcData($ticket, 'fix_time');
        $fixBy = $this->calcService->calcRespondByTime($data);
        return $fixBy;
    }
    
    public function recalculateSlaTimes(tickets $ticket)
    {
        $respondBy = $this->getRespondByDateTime($ticket);
        $fixBy = $this->getFixByDateTime($ticket);
        $tasks = $ticket->slaTasks;
        if ($tasks && $tasks->count()) {
            foreach ($tasks as $task) {
                $task->respond_by = $respondBy;
                $task->fix_by = $fixBy;
                $task->save();
            }
        }
    }

    public function buildCalcData(tickets $ticket, string $field)
    {
        
        $rpt = $ticket->slaApplication->serviceLevelAgreement->response_time;
        $fxt = $ticket->slaApplication->serviceLevelAgreement->fixed_time;
        $stt = $ticket->slaApplication->serviceLevelAgreement->getServiceStartTime();
        $pub = $ticket->slaApplication->serviceLevelAgreement->include_public_holiday;
        $set = $serviceEndAt = $ticket->slaApplication->serviceLevelAgreement->getServiceEndTime();

        $response_time = CaseManagementUtilityServices::buildResponseTimeResult($rpt)['seconds'];
        $service_start_time = CaseManagementUtilityServices::HMSToSeconds($stt);
        $service_end_time = CaseManagementUtilityServices::HMStoSeconds($set);
        $fixed_time = CaseManagementUtilityServices::buildResponseTimeResult($fxt)['seconds'];

        if ($field == 'response_time') {
            $responseTime = $response_time;
        }

        if ($field == 'fix_time') {
            $responseTime = $fixed_time;
        }

        $startDate = strtotime($ticket->created_at);
        $startDay = $ticket->slaApplication->serviceLevelAgreement->start_day->value;
        $endDay = $ticket->slaApplication->serviceLevelAgreement->end_day->value;
        $serviceStartAt = $service_start_time;
        $serviceEndAt = $service_end_time;
        $createdDay = strtolower(date('l', strtotime($ticket->created_at)));
        $includePublicHoliday = $pub;

        return $data = [
            'response_time' => $response_time,
            'fixed_time' => $fixed_time,
            'service_start_time' => $serviceStartAt,
            'service_end_time' => $serviceEndAt,
            'startDate' => $startDate,
            'startDay' => $startDay,
            'endDay' => $endDay,
            'createdDay' => $createdDay,
            'includePublicHoliday' => $includePublicHoliday,
        ];

    }
        
    public function createNewTicketSlaTask(Tickets $ticket, string $respondBy, string $fixBy)
    {
        //dd($data);
        $taskType = config('case-management.taskManagement.type.sla');
        $data = [
            'number' => null,
            'name' => $ticket->short_description,
            'task_type' =>  $taskType,
            'short_description' => $ticket->short_description,
            'ticket_id' => $ticket->id,
            'sla_applications_id' => $ticket->sla_applications_id,
            'respond_by' => $respondBy,
            'fix_by' => $fixBy,
            'state' => CaseState::Open->value,
            'active' => true,
        ];
        $task = $this->caseRepository->createSlaTask($data);
        return $task;
    }

    public static function deleteTicketRelatedRecords(int $ticketId)
    {
        slaTask::where('ticket_id','=',$ticketId)->delete();
        taskLogs::where('ticket_id','=',$ticketId)->delete();
        TaskSiteVisit::where('ticket_id','=',$ticketId)->delete(); 
    }

    public static function restoreTicketRelatedRecords(int $ticketId)
    {
        slaTask::withTrashed()->where('ticket_id','=',$ticketId)->restore();
        taskLogs::withTrashed()->where('ticket_id','=',$ticketId)->restore();
        taskSiteVisit::withTrashed()->where('ticket_id','=',$ticketId)->restore();      
    }

    public function retrieveTicketStatuses()
    {
        $tickets = tickets::where('state','!=', CaseState::Completed->value)
                            ->orWhere('state','!=', CaseState::Cancelled->value)
                            ->orWhereNull('closed_datetime')
                            ->get();
        return $this->caseRetrieveTicketStatus->retrieveStatuses($tickets);
    }

    public function getTotalOpenCases()
    {
        return tickets::where('state','!=', CaseState::Completed->value)
                    ->orWhere('state','!=', CaseState::Cancelled->value)
                    ->orWhereNull('closed_datetime')
                    ->count();    
    }

    public function retrieveTicketStatus(int $caseId)
    {
        
        $ticket = tickets::find($caseId);
        return $this->caseRetrieveTicketStatus->retrieveStatus($ticket);
    }

    // public function retrieveTicketStatus(tickets $ticket)
    // {
    //     //Check breach status
    //     //Check ticket status
    //     //Check urgent log
    //     //Check Site Visit
    //     //Get response time
    //     //get Fix Time
    //     //Check latest note in the logs

    //     $taskLogs = $ticket->taskLogs;
    //     $tasks = $ticket->slaTasks;
    //     $visits = $ticket->siteVisits;
    //     $result = [];
    //     if ($taskLogs && $taskLogs->count()) {
    //         //check urgent log
    //         //check latest Urgent log update
    //         $desc = $taskLogs->sortByDesc('created_at');
    //         $customerLogs = $desc->where('source', '=', 'external');
    //         $lastCustomerLog = $customerLogs->first();
    //         $urgents = $taskLogs->where('require_attention', '=', true)->sortByDesc('created_at');
    //         $first = $urgents->first();
    //         $result["numberOfUrgetLogs"] = 0;
    //         $result["lastUrgentLog"] = null;
    //         $result["lastUrgentLogCreatedAt"] = null;
    //         $result["lastCustomerLog"] = null;
    //         $result["lastCustomerLogCreatedAt"] = null;
    //         $result["totalVisits"] = 0;
    //         $result["totalVisited"] = 0;
    //         $result['currentlyEnroute'] = 0;
    //         $result['currentlyOnsite'] = 0;
    //         $result['respondBy'] = 'Not Available';
    //         $result['fixBy'] = 'Not Available';
    //         $result['respondedAt'] = 'Not Available';
    //         $result['fixedAt'] = 'Not Available'; 
    //         $result['visitScheduled'] = false;
            
    //         $result['resolved'] = $ticket->resolved_datetime ? date('d-m-Y H:i', $ticket->resolved_datetime) : null;
    //         $result['currentlyWorking'] = $ticket->currently_working;
    //         $result['closed'] = $ticket->state == CaseState::Cancelled->value;
            
    //         if ($first && $first->count()) {
    //             $result["numberOfUrgetLogs"] = $urgents->count();
    //             $result["lastUrgentLogCreatedAt"] = date('d-m-Y H:i', strtotime($first->created_at));
    //         }
    //         if ($lastCustomerLog && $lastCustomerLog->count()) {
    //             $result["lastCustomerLogCreatedAt"] = date('d-m-Y H:i', strtotime($lastCustomerLog->created_at));
    //         }
    //     }
    //     if ($tasks && $tasks->count()) {
    //         $tasksDesc = $tasks->sortByDesc('created_at');
    //         $taskBreached = $tasksDesc->where('breached_at', '!=', null);
    //         $fixBreach = $tasks->where('fix_by_breach_at',"!=", null);
    //         $respondByBreach = $tasks->where('respond_by_breach_at',"!=", null);
    //         $firstTask = $tasks->first();
    //         $respondBy = $firstTask->respond_by;
    //         $fixBy = $firstTask->fix_by;
    //         $respondedAt = $firstTask->responded_at;
    //         $fixedAt = $firstTask->fixed_at;
    //         $respondByInfo = $respondBy ? date('d-m-Y H:i', strtotime($respondBy)) : 'Not Available';
    //         $fixByInfo = $fixBy ? date('d-m-Y H:i', strtotime($fixBy)) : 'Not Available';
    //         $respondedAtInfo = $respondedAt ? date('d-m-Y H:i', strtotime($respondedAt)) : 'Not Available';
    //         $fixedAtInfo = $fixedAt ? date('d-m-Y H:i', strtotime($fixedAt)) : 'Not Available';
    //         $result["taskBreached"] = 0;
    //         $result['breachType'] = '';
    //         if ($taskBreached && $taskBreached->count()) {
    //             $result["taskBreached"] = $taskBreached->count();
    //             if($fixBreach && $fixBreach->count()){
    //                 $result['breachType'] = 'Fix By Breach';   
    //             }
    //             if($respondByBreach && $respondByBreach->count()){
    //                 $result['breachType'] = 'Respond By Breach';   
    //             }                
    //         }
    //         $result['respondBy'] = $respondByInfo;
    //         $result['fixBy'] = $fixByInfo;
    //         $result['respondedAt'] = $respondedAtInfo;
    //         $result['fixedAt'] = $fixedAtInfo;
    //     }
    //     if($visits && $visits->count()){
    //         //Total Visits
    //         $visited = $visits->where('onsite_at','!=',null)->count();
    //         $enroute = $visits->where('onsite_at','=', null)
    //             ->where('enroute_at','!=', null)
    //             ->count();
    //         $onsite = $visits->where('onsite_at','!=', null)
    //             ->where('offsite_at','=', null)
    //             ->count();                
    //         $result['totalVisits'] = $visits->count();
    //         $result['totalVisited'] = $visited;
    //         $result['currentlyEnroute'] = $enroute;
    //         $result['currentlyOnsite'] = $onsite;
    //         $result['visitScheduled'] = true;
    //     }
    //     return $result;
    // }

    public function closeTicket(tickets $ticket, slaTask $slaTask, array $data)
    {
        //ticket->closed_datetime
        //ticket->state = closed
        //slaTask->completed_at
        //slaTask->state = completed
        $logAdmin = $this->caseLogAdministrationService;
        $stateTicket = CaseState::Closed->value;
        $logType = config('case-management.logs.type.completed');
        $closedAt = $data["date"] . " " . $data["time"];
        $closedAtTS = strtotime($closedAt);
        $ticket->closed_datetime = $closedAtTS;
        $ticket->state = $stateTicket;
        $result = $ticket->save();
        $slaTask->completed_at = $closedAt;
        $slaTask->state = caseState::Completed->value;
        $slaTask->save();
        
        $logText = $logAdmin->buildCompletedText($closedAt);
        $logAdmin->createSystemLog($ticket, $logText, $logType);

        return $result;
    }

    public function cleanCreateInputData(array $data)
    {
        $currentUserName = Auth::getName(); //auth()->user()->name;
        $data['created_by'] = Auth::id();
        $data['state'] = CaseState::Open->value;
        $data['ticket_number'] = null;
        return $data;        
    }
    
    public function createNewTicket(array $data)
    {
        $service = $this->caseRepository;
        $data = $this->cleanCreateInputData($data);
        $ticket = $service->createNewTicket($data);

        if ($ticket) {            
            //Create Log for ticket
            $logAdmin = $this->caseLogAdministrationService;
            $logText = $logAdmin->buildTicketCreatedText($ticket);
            $logAdmin->createSystemLog($ticket, $logText);
            

            //Get Respond/Fix By DateTime
            $respondby = $this->getRespondByDateTime($ticket);
            $fixby = $this->getFixByDateTime($ticket);
            //Create new task Service
            $task = $this->createNewTicketSlaTask($ticket, $respondby, $fixby);
            //Create log for task
            $logText = $logAdmin->buildSlaTaskCreatedText($task);
            $logType = config('case-management.logs.type.sla_tasks');
            $logAdmin->createSystemLog($ticket, $logText, $logType);
        }
        return $ticket;
    }

    public  function reopenTicket(tickets $ticket)
    {
        
        //Recreate a new ticket using the closed one
        $raisedBy = $ticket->raisedByUser()->first();
        $raisedByUserId = $raisedBy?->user_id;
        $createdBy = Auth::id();
        $data = [
            'ticket_number' => null,
            'customer_id' => $ticket->customer->id,
            'sla_applications_id' => $ticket->sla_applications_id,
            'customer_reference' => $ticket->customer_reference,
            'category' => $ticket->category,
            'created_by' => $createdBy,
            'source' => $ticket->source,
            'list_of_assets' => $ticket->list_of_assets,
            'short_description' => $ticket->short_description,
            'description' => $ticket->description,
            'state' => CaseState::Open->value,
            'raised_by_user' => $raisedByUserId,
            'raised_by_nonuser' => $ticket->raised_by_nonuser,
            'contact_telephone' => $ticket->contact_telephone,
            'contact_email' => $ticket->contact_email,
        ];
        $newTicket = $this->createNewTicket($data);
        if($newTicket){
            $newTicket->closed_ticket_id = $ticket->id;
            $newTicket->save();
            return $newTicket;
        }
        return false;    
    }
        
    public function setSiteVisit(tickets $ticket, array $data)
    {
        $visits = $ticket->siteVisits;
        $result = false;
        $firstVisit = false;
        $assin = json_decode($data["assigned_to"]);
        $dte = date('d-m-Y H:i', strtotime($data["visit_scheduled_at"]));
        if ($visits->count()) {
            $first = $visits->where('first_visit', '=', true)->first();
            $firstVisit = isset($first->first_visit) ? true : false;
            $result = true;
        }
        if ($firstVisit) {
            $data['first_visit'] = false;
        }
        $data['assigned_to'] = $assin->id;

        //Create Site Visit
        $visit = TaskSiteVisit::create($data);
        if ($visit) {
            //Create Log
            $logAdmin = $this->caseLogAdministrationService;
            $currentUserName = Auth::user()->name;
            $logType = config('case-management.logs.type.site_visit');
            $logText = $logAdmin->buildVisitLog($currentUserName, $assin->name, $dte);
            $log = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($log) {
                $log->task_site_visit_id = $visit->id;
                $log->save();
            }
            if (trim($data['description'])) {
                //Create Log for Visit Note
                $logType = config('case-management.logs.type.site_visit_note');
                $logText = $logAdmin->buildVisitNote($currentUserName, $data['description']);
                $visitLog = $logAdmin->createSystemLog($ticket, $logText, $logType);
                $visitLog->task_site_visit_id = $visit->id;
                $visitLog->save();
            }
        }
        return $result;
    }
    
    public function updateSiteVisit(TaskSiteVisit $siteVisit, array $data)
    {
        
        $siteVisitClone = clone $siteVisit;
        foreach($data as $k => $v)
        {
            $siteVisit->$k = $v;
        }        
        $saved = $siteVisit->save();
        $this->trackVisitChangesForLog($siteVisitClone, $data);
        return $saved;
    }
    
    private function trackVisitChangesForLog(TaskSiteVisit $siteVisit, array $data)
    {
        $logAdmin = $this->caseLogAdministrationService;
        $currentUserName = Auth::user()->name;
        $assignedTo = $data["assigned_to"];
        $logType = config('case-management.logs.type.site_visit');
        $ticket = $siteVisit->ticket;
        $currentAssignedTo = $siteVisit->assignedTo->name;        
        $objAssignedTo = User::find($assignedTo,'*');
        $newAssignedTo = $objAssignedTo->name;

        if ($siteVisit->visit_scheduled_at != $data['visit_scheduled_at']) {

            $logText = $logAdmin->buildVisitUpdatedScheduledLog($siteVisit->visit_scheduled_at, $data['visit_scheduled_at'], $currentUserName);
            $vsl = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($vsl) {
                $vsl->task_site_visit_id = $siteVisit->id;
                $vsl->save();
            }
        }
        if ($siteVisit->assigned_to != $data['assigned_to']) {
            //Assigned To has been changed
            $logText = $logAdmin->buildVisitUpdatedAssignedTo($currentAssignedTo, $newAssignedTo, $currentUserName);
            $asl = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($asl) {
                $asl->task_site_visit_id = $siteVisit->id;
                $asl->save();
            }            
        }
        if ($siteVisit->enroute_at != $data['enroute_at']) {
            $logd['user'] = $currentUserName;
            if($data['enroute_at']){
                $logd['action'] = 'update';
                $logd['assignee'] = $newAssignedTo;
                $logd['datetime'] = $data['enroute_at'];
            }else{
                $logd['action'] = 'remove';
            }
            $logType = config('case-management.logs.type.site_visit_enroute');
            $logText = $logAdmin->buildVisitUpdatedEnroute($logd);
            $enl = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($enl) {
                $enl->task_site_visit_id = $siteVisit->id;
                $enl->save();
            }              
        }
        if ($siteVisit->offsite_at != $data['offsite_at']) {
            $logf['user'] = $currentUserName;
            if($data['offsite_at']){
                $logf['action'] = 'update';
                $logf['assignee'] = $newAssignedTo;
                $logf['datetime'] = $data['offsite_at'];
            }else{
                $logf['action'] = 'remove';
            }
            $logType = config('case-management.logs.type.site_visit_offsite');
            $logText = $logAdmin->buildVisitUpdatedOffsite($logf);
            $ofl = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($ofl) {
                $ofl->task_site_visit_id = $siteVisit->id;
                $ofl->save();
            }                             
        }
        if ($siteVisit->onsite_at != $data['onsite_at']) {
            $logn['user'] = $currentUserName;
            if($data['onsite_at']){
                $logn['action'] = 'update';
                $logn['assignee'] = $newAssignedTo;
                $logn['datetime'] = $data['onsite_at'];
            }else{
                $logn['action'] = 'remove';
            }
            $logType = config('case-management.logs.type.site_visit_onsite');
            $logText = $logAdmin->buildVisitUpdatedOnsite($logn);
            $onl = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($onl) {
                $onl->task_site_visit_id = $siteVisit->id;
                $onl->save();
            }            
        }

    }
    
    public function setResolution(tickets $ticket, slaTask $slaTask, array $data)
    {
        /*
            slaTask->status = 'resolved'
            ticket->state = 'Resolved'
            ticket->resolution
            ticket->resolution_details
            ticket->resolved_datetime
        */

        $stateTicket = CaseState::Resolved->value;
        $stateTask = CaseState::Resolved->value;
        $logType = config('case-management.logs.type.resolution');
        $logType = config('case-management.logs.type.resolution');
        $resolvedBy = json_decode($data["resolvedBy"]);
        $rdate = $data["resolvedDate"] . " " . $data["resolvedTime"];
        $rdateTS = strtotime($rdate);
        $ticket->state = $stateTicket;
        $ticket->resolution = $data['resolution'];
        $ticket->resolved_datetime = $rdateTS;
        $ticket->resolution_details = $data["resolutionNote"];
        $ticket->resolved_by = $resolvedBy->id;
        $result = $ticket->save();
        if ($result) {
            $slaTask->state = $stateTask;
            $slaTask->save();
            $logAdmin = $this->caseLogAdministrationService;
            $logText = $logAdmin->buildResolveText($resolvedBy->name, $rdate);
            $log = $logAdmin->createSystemLog($ticket, $logText, $logType);
            if ($data["emailResolution"]) {
                //Email Resolution Log to customer
                $this->sendProgressNotification($ticket, $log, $logText);
                $log->notification_sent_at = date('Y-m-d H:i:s', time());
                $log->save();
            }
        }

        return $result;
    }
    
    private function sendProgressNotification(tickets $ticket, taskLogs $taskLog, string $log)
    {
        
        $from = Auth::user()->name;
        $title = "Progress Log ( $ticket->ticket_number ) - $ticket->short_description";
        $email = '';
        $usr = '';
        if ($ticket->raised_by_user) {
            $usr = $ticket->raised_by_user;
            $user = $ticket->raisedByUser;
            $email = $user->customerUser->email;
            $message["from"] = "From: $from";
            $message["title"] = "Subject: $title";
            $message["greeting"] = "For attention: $usr";
            $message["content"] = $log;
            $message["datetime"] = "Timestamp: " . date('d-m-Y H:s', time());
            $user->notify(new DefaultNotification($message));
        }
        if ($ticket->raised_by_nonuser && $ticket->contact_email) {
            $email = $ticket->contact_email;
            $usr = $ticket->raised_by_nonuser;
            $data["name"] = $usr;
            $data["subject"] = $title;
            $data["title"] = $title;
            $data["message"] = $log;
            Mail::to($email)->send(new DefaultEmail($data));
        }
        if ($email && $usr) {
            //Create Log
            $logAdmin = $this->caseLogAdministrationService;
            $logText = $logAdmin->buildProgressEmailLog($taskLog, $usr);
            $log = $logAdmin->createSystemLog($ticket, $logText);
            return true;
        }
        return false;
    }

    public function setAssignedTo(tickets $ticket, int|string|null $userId)
    {
        if($userId && \is_string(trim($userId))) (int) $userId;
        $user = user::find($userId,'*');        
        $result = $this->caseRepository->updateAssignedTo($ticket, $user);
        if($result) {
            $logAdmin = $this->caseLogAdministrationService;
            $logText = $logAdmin->buildAssignedToText($ticket,  $user);
            $logAdmin->createSystemLog($ticket, $logText);
        }
        return $result;
    }
    
    public function unassignedUser(tickets $ticket)
    {
        $logAdmin = $this->caseLogAdministrationService;
        $logText = $logAdmin->buildUnassignedText($ticket);
        $logAdmin->createSystemLog($ticket, $logText);
        $ticket->assigned_to = null;
        $result = $ticket->save();
        return $result;
    }

    public function slaBreachUpdate(slaTask $tasks)
    {        
        if ($tasks) {
            foreach ($tasks as $task) {
                $this->checkATaskBreach($task, $task->ticket);
            }
        }
    }    
    
    public function checkATaskBreach(slaTask $task, tickets $ticket)
    {
        $now = date('Y-m-d H:i:s');
        $updateBreach = false;
        $r = $this->checkSlaBreach($task);
        if ($r['respond_by_breach']) {
            $task->respond_by_breach_at = $now;
            $updateBreach = true;
        }
        if ($r['fix_by_breach']) {
            $task->fix_by_breach_at = $now;
            $updateBreach = true;
        }
        //Only update breached_at if it has no value
        if ($updateBreach && !$task->breached_at) {
            $task->breached_at = $now;
            $task->save();
            //Prepare and create Log
            $logAdmin = $this->caseLogAdministrationService;
            $logText = $logAdmin->buildBreachLogText($now);
            $logAdmin->createSystemLog($ticket, $logText);
        }
    }
    
    public function checkSlaBreach(slaTask $slaTask)
    {
        $sla = $slaTask;

        $now = time();
        $breach = false;
        $respond_by_breach = false;
        $fix_by_breach = false;
        //$test = [];

        $respondedAt = $sla->responded_at ? strtotime($sla->responded_at) : null;
        $fixedAt = $sla->fixed_at ? strtotime($sla->fixed_at) : null;
        $respby = $sla->respond_by ? strtotime($sla->respond_by) : null;
        $fixby = $sla->fix_by ? strtotime($sla->fix_by) : null;

        //[1] If respondby or fix by have passed current datetime -> breach by default
        //$test[] = "$now (now) > $respby (respby) && !$respondedAt (respondedAt)";
        if ($now > $respby && !$respondedAt) {
            $respond_by_breach = true;
            $breach = true;
            //$test[] = "breach is true";
        }

        //$test[] = "$now (now) > $fixby (fixby) && !$respondedAt (respondedAt)";
        if ($now > $fixby && !$fixedAt) {
            $fix_by_breach = true;
            $breach = true;
            //$test[] = "breach is true";
        }

        /*
            [3] and [4] Check ..
                - if responded_at/fixed_at has value and ..
                - if responded_at/fixed_at is after respond_by/fixed_by and ...
                - if breached_at is empty

        */
        //[3]
        //$test[] = "$respondedAt (respondedAt) && $respondedAt (respondedAt) > $respby (respby) && !$sla->breached_at (sla->breached_at)";
        if ($respondedAt && $respondedAt > $respby && !$sla->breached_at) {
            $respond_by_breach = true;
            $breach = true;
            //$test[] = "breach is true";
        }

        //[4] Check fixed_at
        //$test[]="$fixedAt (fixedAt && $fixedAt (fixedAt) > $fixby (fixby) && && !$sla->breached_at (sla->breached_at)";
        if ($fixedAt && $fixedAt > $fixby && !$sla->breached_at) {
            $fix_by_breach = true;
            $breach = true;
            //$test[] = "breach is true";
        }

        $result = [
            'breach' => $breach,
            'respond_by_breach' => $respond_by_breach,
            'fix_by_breach' => $fix_by_breach,
        ];

        //dd($test);
        return $result;

    }    

    public function updateRespondAt(tickets $ticket, ?string $respondedAt)
    {
        $currentUser = Auth::user()->name;
        $slaTask = $ticket->slaTasks;
        $saved = $this->caseRepository->updateRespondAt($slaTask, $respondedAt);
        if($saved) {
            //Create progress Log
            $service = $this->caseLogAdministrationService;
            $type = config('case-management.logs.type.sla_tasks');
            $rt = $respondedAt ? date_format(date_create($respondedAt), 'd-m-Y H:i') : null;
            $logText = $service->buildRespondAtLogText($rt, $currentUser);
            $service->createSystemLog($ticket, $logText, $type);
        }
        return $saved;
    }

    public function updateFixedAt(tickets $ticket, ?string $fixedAt)
    {
        $currentUser = Auth::user()->name;
        $slaTask = $ticket->slaTasks;
        $saved = $this->caseRepository->updateFixedAt($slaTask, $fixedAt);
        if($saved) {
            $service = $this->caseLogAdministrationService;
            $type = config('case-management.logs.type.sla_tasks');
            $rt = date_format(date_create($fixedAt), 'd-m-Y H:i');
            $logText = $service->buildFixedAtLogText($rt, $currentUser);
            $service->createSystemLog($ticket, $logText, $type);
        }
        return $saved;
    }

    public function buildProgressLog(tickets $ticket, ?string $logText)
    {
        if(!$logText) return false;
        $currentUser = Auth::user()->name;
        $currentDate = now()->format('d-m-y H:i');
        $service = $this->caseLogAdministrationService;
        $logText = $service->buildProgressLogText($logText, $currentDate, $currentUser);
        return $service->createSystemLog($ticket, $logText);        

    }
    

}