<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\TaskList\EmploymentDisputeData;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskLocator;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Translation\TranslatorService;

abstract class BaseSection implements SectionInterface
{
    /**
     * @var array<string, TaskInterface>
     */
    protected array $tasks;
    protected EmploymentDisputeData $employmentDispute;
    protected TranslatorService $translator;
    protected TaskOptions $taskOptions;

    public function __construct(protected TaskLocator $taskLocator)
    {
    }

    /**
     * @return array<string, TaskInterface>
     */
    public function getTasks(): array
    {
        if (empty($this->tasks)) {
            throw new \InvalidArgumentException('You must initialize the tasks array before calling getTasks.');
        }

        foreach ($this->tasks as $task) {
            $task->setSectionId($this->getId());
        }

        return $this->tasks;
    }

    public function getTask(string $sectionId, string $taskId): ?TaskInterface
    {
        if ($sectionId !== $this->getId()) {
            return null;
        }

        foreach ($this->getTasks() as $task) {
            if ($taskId === $task->getId()) {
                return $task;
            }
        }

        return null;
    }

    public function initialize(TranslatorService $translator, TaskOptions $taskOptions, EmploymentDisputeData $employmentDispute): void
    {
        $this->taskOptions = $taskOptions;
        $this->translator = $translator;
        $this->employmentDispute = $employmentDispute;

        $this->initializeSwitcherTasks($employmentDispute);
    }

    private function initializeSwitcherTasks(EmploymentDisputeData $disputeForm): void
    {
        if ($this instanceof SectionSwitcherInterface && $this instanceof SectionInterface) {
            $switcherTask = $this->getSwitcherTask();
            $taskData = $disputeForm->retrieveTaskData($this->getId(), $switcherTask->getDataKey());
            $this->setSwitcherTaskData($taskData);
        }
    }
}
