<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeData;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Translation\TranslatorService;

class SectionRepeater extends BaseSection implements SectionInterface
{
    /**
     * @var array<int, RepeatedSectionInterface&SectionInterface>
     */
    private array $sections;

    /**
     * @param array<string, mixed>                      $sectionCounts
     * @param RepeatedSectionInterface&SectionInterface $prototype
     */
    public function __construct(private array $sectionCounts, private $prototype)
    {
        $sections = [];
        foreach ($this->sectionCounts as $id => $type) {
            if ($type === $this->getType()) {
                $sections[] = $this->createSection($id);
            }
        }
        $this->sections = $sections;
    }

    public function initializeTasks(TaskOptions $taskOptions): void
    {
        foreach ($this->getSections() as $section) {
            $section->initialize($this->translator, $taskOptions, $this->employmentDispute);
            $section->initializeTasks($taskOptions);
        }
    }

    public function initialize(TranslatorService $translator, TaskOptions $taskOptions, EmploymentDisputeData $employmentDispute): void
    {
        parent::initialize($translator, $taskOptions, $employmentDispute);
        $this->prototype->initialize($translator, $taskOptions, $employmentDispute);
    }

    public function getType(): string
    {
        return $this->prototype->getType();
    }

    /**
     * @return RepeatedSectionInterface&SectionInterface
     */
    public function createSection(string $id)
    {
        $section = clone $this->prototype;
        $section->setId($id);

        return $section;
    }

    /**
     * @return array<int, RepeatedSectionInterface&SectionInterface>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function getId(): string
    {
        return 'multiple';
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks(bool $flatten = true): array
    {
        $tasks = [];
        foreach ($this->getSections() as $section) {
            foreach ($section->getTasks() as $task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    public function getTask(string $sectionId, string $taskId): ?TaskInterface
    {
        foreach ($this->getSections() as $section) {
            if ($section->getId() !== $sectionId) {
                continue;
            }

            foreach ($section->getTasks() as $task) {
                if ($task->getId() === $taskId) {
                    return $task;
                }
            }
        }

        return null;
    }

    public function getLabel(): string
    {
        return $this->prototype->getLabel();
    }

    public function getReviewPageLabel(): string
    {
        return $this->prototype->getReviewPageLabel();
    }

    public function generateId(): string
    {
        return $this->getType().'-'.uniqid();
    }

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void
    {
        foreach ($this->getSections() as $section) {
            $section->applyPayload($payload, $storage);
        }
    }
}
