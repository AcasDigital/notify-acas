<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Tasks\TaskInterface;

interface SectionSwitcherInterface
{
    public function getSwitcherTask(): TaskInterface;

    public function setSwitcherTaskData(mixed $data): void;

    public function getSwitcherTaskData(): mixed;
}
