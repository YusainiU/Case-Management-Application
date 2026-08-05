<?php

namespace Simplydigital\NotificationManagement\Notifications\Exceptions;

use Exception;
class MissingTemplateException extends Exception
{
    public function __construct(
        string $message = 'A notification template is required.'
    ) {
        parent::__construct($message);
    }    

}