<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

class NotificationResult
{
    public function __construct(

        public bool $success,

        public ?string $transport,

        public ?string $messageId,

        public ?string $response = null

    ) {}

}