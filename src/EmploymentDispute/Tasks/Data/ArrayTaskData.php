<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class ArrayTaskData implements TaskDataInterface
{
    /**
     * @var string[]
     */
    private ?array $data = [];

    /**
     * @return string[]
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param string[] $data
     */
    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->getData());
    }
}
