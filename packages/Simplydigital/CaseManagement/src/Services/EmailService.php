<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\NotificationManagement\Notifications\DTO\EmailRecipient;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmailService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function caseCreated(tickets $case)
    {

        $createdBy = $case->createdBy->email;
        $createdByName = $case->createdBy->name;

        $raisedBy = [];
        $bcc = [];
        $cc = [
            new EmailRecipient(
                email: $createdBy,
                name: $createdByName
            ),
        ];

        if ($case->raised_by_user) {
            $raisedBy = [
                new EmailRecipient(
                    email: $case->raisedByUser->customerUser->email,
                    name: $raisedByName = $case->raisedByUser->customerUser->name
                ),
            ];

        } elseif ($case->raised_by_nonuser && $case->contact_email) {
            $raisedby = [
                new EmailRecipient(
                    email: $raisedBy = $case->contact_email,
                    name: $raisedByName = $case->contact_email
                ),
            ];
        }

        if ($case->assigned_to) {
            $cc[] = new EmailRecipient(
                email: $case->assignedToUser->email,
                name: $case->assignedToUser->name
            );
        }

        if (! \count($raisedBy)) {
            return false;
        }

        $to = $raisedBy;
        $subject = "{$case->number}: {$case->short_description}";

        $payload = new NotificationViewData(
            subject: $subject,
            greeting: 'New Case Created',
            intro: $case->short_description,
            details: [
                'Site' => $case->customer->name,
                'Created By' => $case->createdBy->name,
                'Our Reference' => $case->number,
                'Your Reference' => $case->customer_reference,
                'Created At' => $case->created_at->format('d-m-Y H:i'),
            ],
            body: "Case Description: {$case->description}",
            actionText: 'Open Service Log',
        );

        $data = new NotificationData(
            subject: $subject,
            to: $to,
            viewData: $payload,
            trigger: $case,
            cc: $cc,
            bcc: $bcc,
            createdBy: $case->createdBy,
        );

        $this->notificationService->send($data);
    }

    public function caseAssignment(tickets $case)
    {
        if ($case->assigned_to) {
            $assigned = $case->assignedToUser->name;
            $to = [
                new EmailRecipient(
                    email: $case->assignedToUser->email,
                    name: $assigned
                ),
            ];
            $cc = [];
            $bcc = [];
            $subject = 'Case Assignment';
            $payload = new NotificationViewData(
                subject: $subject,
                greeting: 'Case Assignment',
                intro: "This case {$case->number} has been assigned to you",
                details: [
                    'Site' => $case->customer->name,
                    'Created By' => $case->createdBy->name,
                    'Our Reference' => $case->number,
                    'Customer Reference' => $case->customer_reference,
                    'Created At' => $case->created_at->format('d-m-Y H:i'),
                ],
                body: "Case Description: {$case->description}",
                actionText: 'Open Service Log',
            );
            $data = new NotificationData(
                subject: $subject,
                to: $to,
                viewData: $payload,
                trigger: $case,
                cc: $cc,
                bcc: $bcc,
                createdBy: $case->createdBy,
            );
            $this->notificationService->send($data);
        }
    }

    public function caseResolved(tickets $case)
    {
        $customerContact = [];
        $cc = [];
        $bcc = [];
        $resolutionType = $case->resolution;
        $resolution = $case->resolution_details;
        $resolvedBy = $case->resolvedBy;
        $datetime = Carbon::createFromTimestamp($case->resolved_datetime)->format('d-m-Y H:i');
        if ($case->raised_by_user) {
            $customerContact = [
                new EmailRecipient(
                    email: $case->raisedByUser->customerUser->email,
                    name: $raisedByName = $case->raisedByUser->customerUser->name
                ),
            ];

        } elseif ($case->raised_by_nonuser && $case->contact_email) {
            $customerContact = [
                new EmailRecipient(
                    email: $raisedBy = $case->contact_email,
                    name: $raisedByName = $case->contact_email
                ),
            ];
        }
        if(!\count($customerContact)) return false;
        
        $greeting = "This case {$case->number} has been resolved";
        $subject = "Case Resolution";

        $payload = new NotificationViewData(
            subject: $subject,
            greeting: $greeting,
            intro: $case->short_description,
            details: [
                'Site' => $case->customer->name,
                'Created By' => $case->createdBy->name,
                'Our Reference' => $case->number,
                'Your Reference' => $case->customer_reference,
                'Created At' => $case->created_at->format('d-m-Y H:i'),
                'Resolved By' => $resolvedBy->name,
                'Resolution Type' => $resolutionType,
                'Resolved At' => $datetime,
            ],
            body: "Resolution Details: {$resolution}",
            actionText: 'Open Service Log',            
        );
        $data = new NotificationData(
            subject: $subject,
            to: $customerContact,
            viewData: $payload,
            trigger: $case,
            cc: $cc,
            bcc: $bcc,
            createdBy: $case->createdBy,
        );
        $this->notificationService->send($data);                
        
    }

    public function caseClosed(tickets $case)
    {
        $customerContact = [];
        $cc = [];
        $bcc = [];
        $resolutionType = $case->resolution;
        $resolution = $case->resolution_details;
        $resolvedBy = $case->resolvedBy;        
        $datetime = Carbon::createFromTimestamp($case->resolved_datetime)->format('d-m-Y H:i');
        $closed = Carbon::createFromTimestamp($case->closed_datetime)->format('d-m-Y H:i');
        $closedBy = Auth::user()->name;
        $closedByEmail = Auth::user()->email;        
        if ($case->raised_by_user) {
            $customerContact = [
                new EmailRecipient(
                    email: $case->raisedByUser->customerUser->email,
                    name: $raisedByName = $case->raisedByUser->customerUser->name
                ),
            ];

        } elseif ($case->raised_by_nonuser && $case->contact_email) {
            $customerContact = [
                new EmailRecipient(
                    email: $raisedBy = $case->contact_email,
                    name: $raisedByName = $case->contact_email
                ),
            ];
        }
        if($closedByEmail){
            $cc = [
                new EmailRecipient(
                    name: $closedBy,
                    email: $closedByEmail
                ),
            ];
        }

        if(!\count($customerContact)) return false;
        
        $greeting = "This case {$case->number} has been closed";
        $subject = "Case Closure";

        $payload = new NotificationViewData(
            subject: $subject,
            greeting: $greeting,
            intro: $case->short_description,
            details: [
                'Site' => $case->customer->name,
                'Created By' => $case->createdBy->name,
                'Our Reference' => $case->number,
                'Your Reference' => $case->customer_reference,
                'Created At' => $case->created_at->format('d-m-Y H:i'),
                'Resolved By' => $resolvedBy->name,
                'Resolution Type' => $resolutionType,
                'Resolved At' => $datetime,
                'Closed At' => $closed,
                'Closed By' => $closedBy,
                'Closed Email' => $closedByEmail,
            ],
            body: "Resolution Details: {$resolution}",
            actionText: 'Open Service Log',            
        );
        $data = new NotificationData(
            subject: $subject,
            to: $customerContact,
            viewData: $payload,
            trigger: $case,
            cc: $cc,
            bcc: $bcc,
            createdBy: $case->createdBy,
        );
        $this->notificationService->send($data);            
    }

    public function notifyCustomer(TaskLogs $log)
    {
        $case = $log->ticket;
        $caseNumber = $case->number;
        $logNumber = $log->number;
        $logtext = $log->description;
        $loggedBy = Auth::user()->name;
        $loggedByEmail= Auth::user()->email;         
        $customerContact = [];
        $cc = [];
        $bcc = [];
        if ($case->raised_by_user) {
            $customerContact = [
                new EmailRecipient(
                    email: $case->raisedByUser->customerUser->email,
                    name: $raisedByName = $case->raisedByUser->customerUser->name
                ),
            ];

        } elseif ($case->raised_by_nonuser && $case->contact_email) {
            $customerContact = [
                new EmailRecipient(
                    email: $raisedBy = $case->contact_email,
                    name: $raisedByName = $case->contact_email
                ),
            ];
        }

        if(!\count($customerContact)) return false;
        
        $greeting = "Progress Notification for case # {$caseNumber}";
        $subject = "Progress Notification";
        
        $payload = new NotificationViewData(
            subject: $subject,
            greeting: $greeting,
            intro: "Case short description {$case->short_description}",
            details: [
                'Site' => $case->customer->name,
                'Progress created By' => $loggedBy,
                'Our Reference' => $case->number,
                'Your Reference' => $case->customer_reference,
                'Created At' => $log->created_at->format('d-m-Y H:i'),
                'Log Ref #' => $logNumber,
            ],
            body: "Progress Log: {$logtext}",
            actionText: 'Open Service Log',            
        );
        $data = new NotificationData(
            subject: $subject,
            to: $customerContact,
            viewData: $payload,
            trigger: $case,
            cc: $cc,
            bcc: $bcc,
            createdBy: $case->createdBy,
        );
         $this->notificationService->send($data);  

    }

}
