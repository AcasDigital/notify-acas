<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HolidayDateTaskData implements TaskDataInterface
{
    private ?\DateTime $start = null;
    #[Assert\Valid]
    private ?\DateTime $end = null;

    /**
     * We need this custom validation callback to set the custom holiday error messages.
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->getStart() && $this->getEnd() && $this->getStart() > $this->getEnd()) {
            $context->buildViolation('The date your holiday year starts needs to be before the date it ends')
                ->atPath('start')
                ->addViolation();
        }

        if ($this->getStart() && $this->getEnd() && $this->getStart() > $this->getEnd()) {
            $context->buildViolation('The date your holiday year ends needs to be after the date it starts')
                ->atPath('end')
                ->addViolation();
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->getStart()) && empty($this->getEnd());
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(?\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }
}
