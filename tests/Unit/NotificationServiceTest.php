<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Mail;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\NotificationManagement\Notifications\DTO\EmailRecipient;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Notifications\Jobs\SendNotificationJob;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    private function buildTestData()
    {
        $case = tickets::find(1);
        $createdBy = User::find(7, '*');

        $em1 = 'yusaini.usulludin@simplydigital.com';
        $em2 = 'yusaini.usulludin@example.com';
        $em3 = 'rick.pearce@simplydigital.com';
        $em4 = 'joen@example.com';

        $to = [
            new EmailRecipient(
                email: $em1,
                name: 'Yusaini Usullidin'),
        ];

        $cc = [
            new EmailRecipient(
                email: $em1,
                name: 'Yusaini Usullidin'),
            new EmailRecipient(
                email: $em2,
                name: 'Yusaini Usullidin'),
        ];

        $bcc = [
            new EmailRecipient(
                email: $em3,
                name: 'Rick Pearce'),
            new EmailRecipient(
                email: $em4,
                name: 'Joe Blogg'),
        ];

        // $service = new NotificationService;

        $payload = new NotificationViewData(
            subject: 'Test Notification',
            greeting: 'Welcome Test # 2',
            intro: 'This is a test email # 2',
            details: [
                'Customer' => 'ABC Ltd',
                'Engineer' => 'Simon Jones',
                'Reference' => 'LOG-000552',
                'Priority' => 'Urgent',
            ],
            body: 'Please review this service log immediately.',
            actionText: 'Open Service Log',
        );

        $data = new NotificationData(
            subject: 'Test Notification',
            to: $to,
            viewData: $payload,
            trigger: $case,
            cc: $cc,
            bcc: $bcc,
            // scheduledAt:Carbon::now()->toString(),
            createdBy: $createdBy,
        );

        return $data;
    }

    public function test_using_raw_mail()
    {
        // Send raw text email
        Mail::raw('This is the email body', function ($message) {
            $message->to('text@example.com')
                ->subject('Test Email');
        });

    }

    public function test_notification(): void
    {

        // !! IMPORTANT !!
        // For this to work permanently change in phpunit.xml:
        // <env name="MAIL_MAILER" value="smtp"/>

        $service = new NotificationService;
        $data = $this->buildTestData();

        // Use the service to send email
        $notification = $service->send($data);

        // Wait a moment for the email to send
        sleep(2);

        $this->assertNotNull($notification);

    }

    public function test_mail_with_queue()
    {
        $data = $this->buildTestData();
        $service = new NotificationService;
        Queue::fake();
        $notification = $service->send($data);
        Queue::assertPushed(
            SendNotificationJob::class,
            function ($job) use ($notification) {
                return $job->notification->is($notification);
            }
        );
    }
}
