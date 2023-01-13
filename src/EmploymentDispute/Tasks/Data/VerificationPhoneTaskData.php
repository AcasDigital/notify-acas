<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Validator as A12Assert;

class VerificationPhoneTaskData extends VerificationTaskData implements TaskDataInterface
{
    private ?string $code = null;

    #[A12Assert\VerificationPhoneRequirements]
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
