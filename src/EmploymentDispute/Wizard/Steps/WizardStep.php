<?php

namespace App\EmploymentDispute\Wizard\Steps;

use App\EmploymentDispute\Tasks\TaskInterface;

class WizardStep implements WizardStepInterface
{
    public function __construct(private TaskInterface $task, private ?\Closure $skipCallback = null)
    {
    }

    public function getLabel(): string
    {
        return $this->task->getLabel();
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function shouldSkip(): bool
    {
        if (is_callable($this->skipCallback)) {
            $callable = $this->skipCallback;

            return $callable();
        }

        return false;
    }
}
