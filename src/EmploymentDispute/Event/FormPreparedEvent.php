<?php

namespace App\EmploymentDispute\Event;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class FormPreparedEvent
{
    public function __construct(private TaskDataInterface $formData, private string $id)
    {
    }

    public function getData(): TaskDataInterface
    {
        return $this->formData;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
