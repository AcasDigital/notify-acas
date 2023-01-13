<?php

namespace App\EmploymentDispute\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class DisputeFormToReturnToExists extends Constraint
{
    public string $message = 'No form found. Check the save and return code and memorable word you entered are correct';
}
