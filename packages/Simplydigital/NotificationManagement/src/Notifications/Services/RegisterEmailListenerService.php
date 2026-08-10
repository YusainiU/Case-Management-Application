<?php

namespace Simplydigital\NotificationManagement\Notifications\Services;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Mime\Address;
class RegisterEmailListenerService
{

    public static function registerListener()
    {

        Event::listen(MessageSending::class, function (MessageSending $event) {

            $message = $event->message;

            logger()->info('MessageSending', [
                'subject' => $message->getSubject(),
                'from' => collect($message->getFrom())->map(fn (Address $a) => [
                    'address' => $a->getAddress(),
                    'name' => $a->getName(),
                ])->all(),
                'to' => collect($message->getTo())->map(fn (Address $a) => [
                    'address' => $a->getAddress(),
                    'name' => $a->getName(),
                ])->all(),
                'cc' => collect($message->getCc())->map(fn (Address $a) => [
                    'address' => $a->getAddress(),
                    'name' => $a->getName(),
                ])->all(),
                'bcc' => collect($message->getBcc())->map(fn (Address $a) => [
                    'address' => $a->getAddress(),
                    'name' => $a->getName(),
                ])->all(),
            ]);

        });
    
    }

}