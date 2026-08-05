<?php

namespace Simplydigital\NotificationManagement\Notifications\Exceptions;

use Exception;

class AttachmentNotFoundException extends Exception
{
    public function __construct(
        string $message = 'The requested attachment was not found.'
    ) {
        parent::__construct($message);
    }    
}