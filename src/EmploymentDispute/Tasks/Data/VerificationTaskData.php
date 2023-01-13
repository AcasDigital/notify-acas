<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class VerificationTaskData implements TaskDataInterface
{
    private ?string $code = null;
    private bool $requestforResend = false;

    public function isEmpty(): bool
    {
        return false;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getRequestforResend(): bool
    {
        return $this->requestforResend;
    }

    public function setRequestforResend(bool $requestforResend): self
    {
        $this->requestforResend = $requestforResend;

        return $this;
    }
}
