<?php

namespace App\EmploymentDispute\Event;

class ResetMemorableLinkEvent
{
    public function __construct(private string $email, private string $id)
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
