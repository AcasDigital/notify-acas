<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;

class StringTaskData implements TaskDataInterface, StringDataInterface
{
    #[Assert\NotBlank]
    private ?string $data = '';

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->getData());
    }
}
