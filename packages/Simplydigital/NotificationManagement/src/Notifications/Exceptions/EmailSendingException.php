<?php

namespace Simplydigital\NotificationManagement\Notifications\Exceptions;

use Exception;

class EmailSendingException extends Exception
{
    public function __construct(
        string $message = 'Failed to send the Message.'
    ) {
        parent::__construct($message);
    }    
}