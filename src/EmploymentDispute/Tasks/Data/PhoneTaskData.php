<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\PhoneData;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneTaskData implements TaskDataInterface
{
    #[Assert\Valid]
    private ?PhoneData $phone = null;

    #[Ignore]
    public function isEmpty(): bool
    {
        return $this->getPhone() && $this->getPhone()->isEmpty();
    }

    public function getPhone(): ?PhoneData
    {
        return $this->phone;
    }

    public function setPhone(?PhoneData $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
