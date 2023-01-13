<?php

namespace App\Form\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class OptionalAddressData implements TaskDataInterface
{
    #[Assert\Length(max: 250)]
    private ?string $addressFirstLine = null;

    #[Assert\Length(max: 250)]
    private ?string $addressSecondLine = null;

    #[Assert\Length(max: 100)]
    private ?string $town = null;

    #[Assert\Length(max: 20)]
    private ?string $postcode = null;

    #[Ignore]
    public function isEmpty(): bool
    {
        return empty($this->getAddressFirstLine()) && empty($this->getTown()) && empty($this->getPostcode());
    }

    public function getAddressFirstLine(): ?string
    {
        return $this->addressFirstLine;
    }

    public function setAddressFirstLine(?string $addressFirstLine): self
    {
        $this->addressFirstLine = $addressFirstLine;

        return $this;
    }

    public function getAddressSecondLine(): ?string
    {
        return $this->addressSecondLine;
    }

    public function setAddressSecondLine(?string $addressSecondLine): self
    {
        $this->addressSecondLine = $addressSecondLine;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }
}
