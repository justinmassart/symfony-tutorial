<?php

namespace App\Message;

final class SendContactEmailMessage
{
    public function __construct(
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly string $email,
        private readonly string $content,
    ) {
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
