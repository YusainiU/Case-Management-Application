<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

final readonly class NotificationViewData
{
    public function __construct(
        public string $subject,
        public ?string $greeting = null,
        public ?string $intro = null,
        public array $details = [],
        public ?string $body = null,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
    ) {}

    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'greeting' => $this->greeting,
            'intro' => $this->intro,
            'details' => $this->details,
            'body' => $this->body,
            'actionUrl' => $this->actionUrl,
            'actionText' => $this->actionText,
        ];
    }    
    
} 
