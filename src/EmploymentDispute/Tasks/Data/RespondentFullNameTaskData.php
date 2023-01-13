<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RespondentFullNameTaskData extends FullNameTaskData implements TaskDataInterface, FullNameDataInterface
{
    /**
     * We need this custom validation callback to set the task status on the frontend.
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!$this->getFullName() || $this->getFullName()->isEmpty()) {
            if (!$this->getFullName()?->getFirstName()) {
                $context->buildViolation("Enter the respondent's first name (including middle names)")
                    ->atPath('fullName.firstName')
                    ->addViolation();
            }
            if (!$this->getFullName()?->getLastName()) {
                $context->buildViolation("Enter the respondent's last name or family name​")
                ->atPath('fullName.lastName')
                ->addViolation();
            }
        }
    }
}
