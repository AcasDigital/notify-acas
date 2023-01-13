<?php

namespace App\EmploymentDispute\Wizard\Steps;

use App\EmploymentDispute\Tasks\TaskInterface;

interface WizardStepInterface
{
    public function getLabel(): string;

    public function getTask(): TaskInterface;

    public function shouldSkip(): bool;
}
