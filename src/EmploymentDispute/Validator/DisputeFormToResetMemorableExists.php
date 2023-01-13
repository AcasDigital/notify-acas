<?php

namespace App\EmploymentDispute\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class DisputeFormToResetMemorableExists extends Constraint
{
    public string $message = 'Check the email address and save and return code you entered are correct';
}
