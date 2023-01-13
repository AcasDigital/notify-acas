<?php

namespace App\EmploymentDispute\Validator;

use App\EmploymentDispute\Validator as A12Assert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[\Attribute]
class VerificationPhoneRequirements extends Compound
{
    /**
     * @param array<mixed> $options
     *
     * @return Constraint[]
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(message: 'Enter the confirmation code you received by text. The code is 5 numbers only.'),
            new Assert\Length(min: 5, max: 5, minMessage: '', maxMessage: '', exactMessage: ''),
            new Assert\Regex('/^[1-9]{1}[0-9]{4}$/', message: ''),
            new A12Assert\VerificationCodeIsValid(),
        ];
    }
}
