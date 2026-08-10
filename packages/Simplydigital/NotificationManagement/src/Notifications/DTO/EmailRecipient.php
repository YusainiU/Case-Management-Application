<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

use InvalidArgumentException;
class EmailRecipient
{
    public function __construct(

        public string $email,

        public ?string $name = null
    ){

        $email = trim($this->email);

        if ($email === '') {
            throw new InvalidArgumentException(
                'Email address cannot be empty.'
            );
        }    

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                "Invalid email address: {$this->email}"
            );
        }

        $this->email = $email;

    }

}