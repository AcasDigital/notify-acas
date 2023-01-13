<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\OptionalAddressData;
use Symfony\Component\Serializer\Annotation\Ignore;

class OptionalAddressTaskData implements TaskDataInterface
{
    private ?OptionalAddressData $address = null;

    #[Ignore]
    public function isEmpty(): bool
    {
        return $this->getAddress()?->isEmpty() ?? true;
    }

    public function getAddress(): ?OptionalAddressData
    {
        return $this->address;
    }

    public function setAddress(?OptionalAddressData $address): self
    {
        $this->address = $address;

        return $this;
    }
}
