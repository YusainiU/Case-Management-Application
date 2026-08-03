<?php

namespace Simplydigital\NotificationManagement\Exceptions;

use Exception;

class MissingSubjectException extends Exception
{
    public function __construct(
        string $message = 'A notification subject is required.'
    ) {
        parent::__construct($message);
    }    
}