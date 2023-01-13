<?php

namespace App\EmploymentDispute\Event;

use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;

class SaveAndReturnEmailEvent
{
    public function __construct(private OptionalEmailTaskData $formData, private string $id)
    {
    }

    public function getData(): OptionalEmailTaskData
    {
        return $this->formData;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
