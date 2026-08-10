<?php

namespace Simplydigital\NotificationManagement\Notifications\Exceptions;

use Exception;

class InvalidArgumentException extends Exception
{
    public function __construct(
        string $message = 'Invalid Arguments Exceptions .'
    ) {
        parent::__construct($message);
    }    
}