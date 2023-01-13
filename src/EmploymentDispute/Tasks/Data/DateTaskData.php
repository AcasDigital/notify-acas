<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class DateTaskData implements TaskDataInterface
{
    private ?\DateTime $start = null;

    public function isEmpty(): bool
    {
        return empty($this->getStart());
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(?\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }
}
