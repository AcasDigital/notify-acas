<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\PhoneWithoutVerificationData;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneWithoutVerificationTaskData implements TaskDataInterface
{
    #[Assert\Valid]
    private ?PhoneWithoutVerificationData $phone = null;

    #[Ignore]
    public function isEmpty(): bool
    {
        return $this->getPhone() && $this->getPhone()->isEmpty();
    }

    public function getPhone(): ?PhoneWithoutVerificationData
    {
        return $this->phone;
    }

    public function setPhone(?PhoneWithoutVerificationData $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
