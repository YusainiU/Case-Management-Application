<?php

namespace Simplydigital\NotificationManagement\Notifications\DTO;

class EmailRecipient
{
    public function __construct(

        public string $name,

        public string $email

    ){}

}