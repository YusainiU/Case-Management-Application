<?php

namespace Simplydigital\NotificationManagement\Exceptions;

use Exception;

class InvalidChannelException extends Exception
{
    public function __construct(
        string $message = 'Invalid Channel'
    ) {
        parent::__construct($message);
    }    
}