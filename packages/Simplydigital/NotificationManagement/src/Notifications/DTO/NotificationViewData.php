<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

final readonly class NotificationViewData
{
    public function __construct(
        public string $subject,
        public ?string $greeting = null,
        public ?string $intro = null,
        public array $details = [],
        public ?string $message = null,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
    ) {}
    
} 
