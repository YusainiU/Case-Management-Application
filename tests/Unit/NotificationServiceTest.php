<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Simplydigital\NotificationManagement\Notifications\Mail\NotificationMail;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\NotificationManagement\Notifications\DTO\{
    NotificationData,
    NotificationViewData,
};
//use Carbon\Carbon;
//use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

//use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;

class NotificationServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_notification(): void
    {
        $case = tickets::find(1);
        $createdBy = User::find(7,'*');

        $n = Notification::find(4,'*');

        $service = new NotificationService();
        $payload = new NotificationViewData(
            subject:'Test Notification',
            greeting:'Welcome Test # 2',
            intro:'This is a test email # 2',
            details: [
                'Customer' => 'ABC Ltd',
                'Engineer' => 'Simon Jones',
                'Reference' => 'LOG-000552',
                'Priority' => 'Urgent',
            ],
            body: 'Please review this service log immediately.',
            actionText:'Open Service Log',            
        );
        $data = new NotificationData(
            subject: 'Test Notification',
            to: ['Yusaini.Usulludin@gmail.com'],
            viewData:$payload,
            trigger:$case,
            cc:['yusaini.usulludin@gmail.com,rick.pearce@gmail.com'],
            bcc:['yusaini.usulludin@gmail.com,rick.pearce@gmail.com'],
            //scheduledAt:Carbon::now()->toString(),
            createdBy:$createdBy,
        );

        dump($service->send($data));
        //dump($data);
        
    }
}
