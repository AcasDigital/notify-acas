<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Validator as A12Assert;

class MemorableWordTaskData implements TaskDataInterface
{
    #[A12Assert\MemorableWordRequirements]
    private ?string $data = '';

    public function getData(): ?string
    {
        return $this->data ? strtoupper($this->data) : $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data ? strtoupper($data) : $data;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->getData());
    }
}
