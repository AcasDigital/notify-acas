<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\FullNameData;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class FullNameTaskData implements TaskDataInterface, FullNameDataInterface
{
    #[Assert\Valid()]
    private ?FullNameData $fullName = null;

    public function getFullName(): ?FullNameData
    {
        return $this->fullName;
    }

    public function setFullName(?FullNameData $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    #[Ignore]
    public function isEmpty(): bool
    {
        return $this->getFullName()?->isEmpty() ?? true;
    }
}
