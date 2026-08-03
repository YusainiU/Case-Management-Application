<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

class NotificationAttachmentData
{
    public function __construct(

        public readonly string $disk,

        public readonly string $path,

        public readonly ?string $filename = null,

    ) {}
}