<?php

namespace Simplydigital\NotificationManagement\Notifications\Exceptions;

use Exception;

class MissingSubjectException extends Exception
{
    public function __construct(
        string $message = 'A notification subject is required.'
    ) {
        parent::__construct($message);
    }    
}