<?php

namespace App\Message;

class RunJobMessage
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
