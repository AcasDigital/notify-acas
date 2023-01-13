<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;

class ConfirmationCodeResendTaskData implements TaskDataInterface
{
    public function isEmpty(): bool
    {
        return false;
    }
}
