<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\NotificationManagement\Notifications\DTO\{
    NotificationData,
    NotificationViewData,
};

//use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;

class NotificationServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_notification(): void
    {
        $case = tickets::find(1);
        $service = new NotificationService();
        $payload = new NotificationViewData(
            subject:'Test Notification',
        );
        $data = new NotificationData(
            subject: 'Test Notification',
            to: ['Yusaini.Usulludin@gmail.com'],
            viewData:$payload,
            trigger:$case,
        );

        //$service->send($data);
        dump($data);
        
    }
}
