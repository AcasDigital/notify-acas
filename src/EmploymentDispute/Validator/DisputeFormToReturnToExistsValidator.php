<?php

namespace App\EmploymentDispute\Validator;

use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DisputeFormToReturnToExistsValidator extends ConstraintValidator
{
    public function __construct(private EmploymentDisputeRepository $employmentDisputeRepository)
    {
    }

    /**
     * @param array<string|null> $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DisputeFormToReturnToExists) {
            throw new UnexpectedTypeException($constraint, DisputeFormToReturnToExists::class);
        }

        $returnNumber = $value['SRNnumber'] ?? null;
        $memorableWord = $value['memorableWord'] ?? null;

        if (!$returnNumber || !$memorableWord) {
            throw new \InvalidArgumentException('Return number or memorable word missing');
        }

        $disputeForms = $this->employmentDisputeRepository->findBySRNandMemoWord($returnNumber, $memorableWord);

        if (1 !== count($disputeForms)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
