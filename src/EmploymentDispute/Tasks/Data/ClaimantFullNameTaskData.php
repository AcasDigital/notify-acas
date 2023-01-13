<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Validator as A12Assert;
use App\Form\Data\FullNameData;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ClaimantFullNameTaskData extends FullNameTaskData implements TaskDataInterface, FullNameDataInterface
{
    #[Assert\Valid()]
    #[A12Assert\ContentAwareFullNameValidation]
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

    /**
     * Validation for status checks, no message displayed from here - We need this custom validation callback to set the task status on the frontend.
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!$this->getFullName() || $this->getFullName()->isEmpty()) {
            if (!$this->getFullName()?->getFirstName()) {
                $context->buildViolation('This message is not displayed') // message displays from ContentAwareFullNameValidation
                    ->atPath('fullName')
                    ->addViolation();
            }
            if (!$this->getFullName()?->getLastName()) {
                $context->buildViolation('This message is not displayed​') // message displays from ContentAwareFullNameValidation
                ->atPath('fullName')
                ->addViolation();
            }
        }
    }
}
