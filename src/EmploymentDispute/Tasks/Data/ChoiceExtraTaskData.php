<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\ChoiceExtraData;
use Symfony\Component\Validator\Constraints as Assert;

class ChoiceExtraTaskData implements TaskDataInterface
{
    #[Assert\Valid]
    private ?ChoiceExtraData $data = null;

    public function isEmpty(): bool
    {
        if (null === $this->getData()) {
            return true;
        }

        return $this->getData()->isEmpty();
    }

    public function getData(): ?ChoiceExtraData
    {
        return $this->data;
    }

    public function setData(?ChoiceExtraData $data): self
    {
        $this->data = $data;

        return $this;
    }
}
