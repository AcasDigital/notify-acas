<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTaskData implements TaskDataInterface
{
    #[Assert\Positive]
    private ?float $data = null;

    public function getData(): ?float
    {
        return $this->data;
    }

    public function setData(?float $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->getData());
    }
}
