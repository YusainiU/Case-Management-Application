<?php

namespace SimplyDigital\NotificationManagement;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Symfony\Component\Mime\Address;


class NotificationManagementServiceProvider extends ServiceProvider
{
    private array $services = [
        'notification-services' => NotificationService::class,
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views with notification namespace
        $this->loadViewsFrom(__DIR__.'/Notifications/Templates', 'notification');

        $this->registerServices();

        $this->registerListener();

    }

    public function registerServices()
    {
        foreach ($this->services as $key => $service) {
            $this->app->singleton($key, fn () => new $service);
        }
    }

    public function registerListener()
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
