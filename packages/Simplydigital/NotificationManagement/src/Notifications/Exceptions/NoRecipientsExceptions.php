<?php

namespace Simplydigital\NotificationManagement\Exceptions;

use Exception;

class NoRecipientsException extends Exception
{
    public function __construct(
        string $message = 'No recipients were provided.'
    ) {
        parent::__construct($message);
    }
}