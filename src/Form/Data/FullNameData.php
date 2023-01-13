<?php

namespace App\Form\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FullNameData implements TaskDataInterface
{
    #[Assert\Length(max: 100)]
    private ?string $firstName = null;

    #[Assert\Length(max: 100)]
    private ?string $lastName = null;

    public function isEmpty(): bool
    {
        return empty($this->getFirstName()) || empty($this->getLastName());
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
}
