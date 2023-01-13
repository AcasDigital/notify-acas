<?php

namespace App\EmploymentDispute\Wizard\Event;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class StepCompletedEvent
{
    public function __construct(private TaskDataInterface $formData)
    {
    }

    public function getData(): TaskDataInterface
    {
        return $this->formData;
    }
}
