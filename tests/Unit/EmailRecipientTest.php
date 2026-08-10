<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\NotificationManagement\Models\Notification;
use Simplydigital\NotificationManagement\Notifications\DTO\EmailRecipient;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationViewData;
use Simplydigital\NotificationManagement\Notifications\DTO\NotificationData;
use Simplydigital\NotificationManagement\Notifications\Mail\NotificationMail;

use Tests\TestCase;

class EmailRecipientTest extends TestCase
{
    public function test_accepts_valid_email(): void
    {
        $recipient = new EmailRecipient(
            email: 'yusaini.usulludin@simplydigital.com',
            name: 'Yusaini Usullidin',
        );

        $this->assertSame(
            'yusaini.usulludin@simplydigital.com',
            $recipient->email
        );

        $this->assertSame(
            'Yusaini Usullidin',
            $recipient->name
        );
    }

    public function test_rejects_markdown_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EmailRecipient(
            email: '[yusaini.usulludin@simplydigital.com](mailto:yusaini.usulludin@simplydigital.com)',
            name: 'Yusaini Usullidin',
        );
    }

    public function test_notification_recipient_data(): void
    {

        $n = Notification::findOrFail(1);

        foreach ($n->notificationRecipients as $recipient) {
            $this->assertFalse(
                str_contains($recipient->email, 'mailto')
            );

            $this->assertTrue(
                filter_var($recipient->email, FILTER_VALIDATE_EMAIL) !== false
            );
        }

    }

    public function test_email_is_sent_to_correct_recipients(): void
    {
        // Fake the mail system
        Mail::fake();

        // Your code that sends email
        $notification = Notification::find(1);
        foreach ($notification->notificationRecipients as $recipient) {
            Mail::to($recipient->email)->send(new NotificationMail($notification));
        }

        // Assert that emails were sent
        Mail::assertSent(NotificationMail::class, function ($mail) {
            return $mail->hasTo('yusaini.usulludin@simplydigital.com');
        });

    }

    protected function buildTestNotificationData()
    {

        $createdBy = User::find(7);
        $case = tickets::find(1);
    
        $to = [
            new EmailRecipient(
                email: 'yusaini.usulludin@simplydigital.com',
                name: 'Yusaini Usullidin'),
        ];

        $cc = [
            new EmailRecipient(
                email: 'yusaini.usulludin@simplydigital.com',
                name: 'Yusaini Usullidin'),
            new EmailRecipient(
                email: 'yusaini.usulludin@example.com',
                name: 'Yusaini Usullidin'),
        ];

        $bcc = [
            new EmailRecipient(
                email: 'rick.pearce@simplydigital.com',
                name: 'Rick Pearce'),
            new EmailRecipient(
                email: 'joen@example.com',
                name: 'Joe Blogg'),
        ];

        //$service = new NotificationService;
        
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
            createdBy: $createdBy,
        );

    }
    
}
