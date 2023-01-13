<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;

class OptionalEmailTaskData implements TaskDataInterface, StringDataInterface
{
    #[Assert\Email(message: 'Enter an email address in the correct format, like name@example.com')]
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
