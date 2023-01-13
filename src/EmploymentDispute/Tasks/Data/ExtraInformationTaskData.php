<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Form\Data\ExtraInformationData;
use Symfony\Component\Validator\Constraints as Assert;

class ExtraInformationTaskData implements TaskDataInterface
{
    #[Assert\Valid]
    private ?ExtraInformationData $data = null;

    public function isEmpty(): bool
    {
        if (null === $this->getData()) {
            return true;
        }

        return $this->getData()->isEmpty();
    }

    public function getData(): ?ExtraInformationData
    {
        return $this->data;
    }

    public function setData(?ExtraInformationData $data): self
    {
        $this->data = $data;

        return $this;
    }
}
