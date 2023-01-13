<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\FullNameData;
use Symfony\Component\Serializer\Annotation\Ignore;

class OptionalFullNameTaskData implements TaskDataInterface, FullNameDataInterface
{
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
