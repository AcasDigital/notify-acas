<?php

namespace App\EmploymentDispute\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[\Attribute]
class MemorableWordRequirements extends Compound
{
    /**
     * @param array<mixed> $options
     *
     * @return Constraint[]
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Length(min: 6, max: 100, minMessage: 'Enter a memorable word that is 6 characters or more'),
            new Assert\Regex('/^[0-9a-zA-Z]+$/', message: 'This value is not valid. You can only use numbers and letters in your memorable word.'),
        ];
    }
}
