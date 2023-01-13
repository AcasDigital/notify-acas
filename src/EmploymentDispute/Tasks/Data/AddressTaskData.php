<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\AddressData;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AddressTaskData implements TaskDataInterface
{
    #[Assert\Valid]
    private ?AddressData $address = null;

    /**
     * We need this custom validation callback to set the task status on the frontend.
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!$this->getAddress() || $this->getAddress()->isEmpty()) {
            $context->buildViolation('The address fields should not be empty!')
                ->atPath('address')
                ->addViolation();
        }
    }

    #[Ignore]
    public function isEmpty(): bool
    {
        return $this->getAddress()?->isEmpty() ?? true;
    }

    public function getAddress(): ?AddressData
    {
        return $this->address;
    }

    public function setAddress(?AddressData $address): self
    {
        $this->address = $address;

        return $this;
    }
}
