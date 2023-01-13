<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ContactDetailsTaskData implements TaskDataInterface
{
    #[Assert\Email(message: 'Enter an email address in the correct format, like name@example.com')]
    private ?string $email = null;

    private ?string $phone = null;

    public function isEmpty(): bool
    {
        return empty($this->getEmail()) && empty($this->getPhone());
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
