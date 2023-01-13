<?php

namespace App\EmploymentDispute\Validator;

use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DisputeFormToResetMemorableExistsValidator extends ConstraintValidator
{
    public function __construct(private EmploymentDisputeRepository $employmentDisputeRepository)
    {
    }

    /**
     * @param array<string|null> $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DisputeFormToResetMemorableExists) {
            throw new UnexpectedTypeException($constraint, DisputeFormToResetMemorableExists::class);
        }

        $returnNumber = $value['SRNnumber'] ?? null;
        $email = $value['email'] ?? null;

        if (!$returnNumber || !$email) {
            throw new \InvalidArgumentException('Return number or email missing');
        }

        $disputeForms = $this->employmentDisputeRepository->findBySRNandEmail($returnNumber, $email);

        if (1 !== count($disputeForms)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
