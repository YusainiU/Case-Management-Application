<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\NotificationManagement\Notifications\DTO\EmailRecipient;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;

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

        if($case->raised_by_user){
            $raisedBy = [ 
                new EmailRecipient(
                    email: $case->raisedByUser->customerUser->email,
                    name: $raisedByName = $case->raisedByUser->customerUser->name
                )
            ];
        
        }elseif($case->raised_by_nonuser && $case->contact_email) {
            $raisedby = [ 
                new EmailRecipient(
                    email: $raisedBy = $case->contact_email,
                    name: $raisedByName = $case->contact_email
                )
            ];
        }

        if($case->assigned_to) {
            $cc[] = new EmailRecipient(
                email: $case->assignedToUser->email,
                name: $case->assignedToUser->name
            );
        }

        if(!\count($raisedBy)) return false;

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
        if($case->assigned_to) {
            $assigned = $case->assignedToUser->name;
            $to = [
                new EmailRecipient(
                    email: $$case->assignedToUser->email,
                    name: $assigned
                )
            ];
            $cc = [];
            $bcc = [];
            $subject = 'Case Assignment';
        }        
    }

}
