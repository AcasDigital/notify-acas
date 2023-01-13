<?php

namespace App\EmploymentDispute\TaskList;

use App\EmploymentDispute\Sections\ClaimantSection;
use App\EmploymentDispute\Sections\ReasonsForDisputeSection;
use App\EmploymentDispute\Sections\RepresentativeSection;
use App\EmploymentDispute\Sections\RespondentSection;
use App\EmploymentDispute\Sections\SectionInterface;
use App\EmploymentDispute\Sections\SectionRepeater;
use App\EmploymentDispute\Sections\SectionSwitcherInterface;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskLocator;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\EmploymentDispute;
use App\Translation\TranslatorService;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskListCreator
{
    private ?string $representingResponse;
    private ?string $earlyConcResponse;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private EmploymentDisputeDataPersister $storage,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private TranslatorService $translator,
        private TaskLocator $taskLocator,
    ) {
    }

    public function initialize(EmploymentDispute $disputeForm): void
    {
        $this->storage->setEmploymentDispute($disputeForm);
        $this->representingResponse = $this->storage->getEmploymentDispute()->getRepresenting();
        $this->earlyConcResponse = $this->storage->getEmploymentDispute()->getType();

        // Add default respondent task is there isn't any.
        $disputeFormData = $this->storage->getFormData();
        $repeatedSectionList = $disputeFormData->getRepeatedSectionList();
        if (empty($repeatedSectionList)) {
            $this->addRepeatedSection('respondent');
        }
    }

    /**
     * @return array<SectionInterface>
     */
    public function getSections(): array
    {
        $disputeForm = $this->storage->getFormData();
        $repeatedSectionList = $disputeForm->getRepeatedSectionList();
        $representing = $this->storage->getEmploymentDispute()->getRepresenting();
        $earlyConcChoice = $this->storage->getEmploymentDispute()->getType();

        $sections = [];
        $sections[] = new ClaimantSection($this->taskLocator);

        if ((TaskOptions::REPRESENTATIVE_MYSELF !== $representing && TaskOptions::FLOW_CERTIFICATE === $earlyConcChoice)
            || TaskOptions::FLOW_CERTIFICATE !== $earlyConcChoice
        ) {
            $sections[] = new RepresentativeSection($this->taskLocator);
        }
        $sections[] = new SectionRepeater($repeatedSectionList, new RespondentSection($this->taskLocator));

        if (TaskOptions::FLOW_CERTIFICATE !== $earlyConcChoice) {
            $sections[] = new ReasonsForDisputeSection($this->taskLocator);
        }

        $this->initializeSections($sections);

        return $sections;
    }

    /**
     * @param array<SectionInterface> $sections
     */
    public function initializeSections(array $sections): void
    {
        $disputeForm = $this->storage->getFormData();

        foreach ($sections as $section) {
            $section->initialize($this->translator, $this->storage->createTaskOptions(), $disputeForm);
            $section->initializeTasks($this->storage->createTaskOptions());
        }
    }

    public function addRepeatedSection(string $type): void
    {
        $section = $this->getSectionRepeater($type);

        $disputeForm = $this->storage->getFormData();
        $disputeForm->addRepeatedSection($section->getType(), $section->generateId());
        $this->storage->setFormData($disputeForm);
    }

    public function removeRepeatedSection(string $sectionId): void
    {
        $disputeForm = $this->storage->getFormData();
        $disputeForm->removeRepeatedSection($sectionId);
        $this->storage->setFormData($disputeForm);
    }

    private function getSectionRepeater(string $type): SectionRepeater
    {
        foreach ($this->getSections() as $section) {
            if ($section instanceof SectionRepeater && $type === $section->getType()) {
                return $section;
            }
        }

        throw new \InvalidArgumentException("Section repeater '$type' not found.");
    }

    public function getTitle(): string
    {
        $disputeForm = $this->storage->getEmploymentDispute();

        if (TaskOptions::FLOW_CERTIFICATE === $disputeForm->getType()) {
            return 'Get a certificate to go to an employment tribunal';
        } else {
            return 'Provide information to start early conciliation';
        }
    }

    public function getTaskForm(string $sectionId, string $taskId): FormInterface
    {
        $task = $this->getTask($sectionId, $taskId);
        $taskData = $this->storage->getTaskData($task);

        $formBuilder = $this->formFactory->createBuilder(FormType::class, $taskData, [
            'data_class' => $task->getDataClass(),
            'empty_data' => $task->getDataClass(),
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);

        return $task->buildForm($formBuilder)->getForm();
    }

    public function getTaskTemplate(string $sectionId, string $taskId): string
    {
        $task = $this->getTask($sectionId, $taskId);

        return $task->getTemplate();
    }

    public function saveTask(string $sectionId, string $taskId, TaskDataInterface $data): void
    {
        $dataId = $this->taskLocator->getTaskById($taskId)->getDataKey();

        $this->storage->setTaskData($sectionId, $dataId, $data);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAllTaskStatus(): array
    {
        $status = [];
        foreach ($this->getSections() as $section) {
            foreach ($section->getTasks() as $task) {
                $status[$task->getSectionId()][$task->getId()] = $this->getTaskStatus($task->getSectionId(), $task->getId());
            }
        }

        return $status;
    }

    public function createTaskOptions(): TaskOptions
    {
        return $this->storage->createTaskOptions();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAllTaskData(bool $showOtherText = true): array
    {
        $taskData = [];
        foreach ($this->getSections() as $section) {
            foreach ($section->getTasks() as $task) {
                if ('reasons_for_dispute' === $task->getId() && $section instanceof SectionSwitcherInterface) {
                    assert($section instanceof ReasonsForDisputeSection);
                    $taskData[$task->getSectionId()][$task->getId()] = $section->getSwitcherTaskLabels($this->storage, $showOtherText);
                } else {
                    $taskData[$task->getSectionId()][$task->getId()] = $this->getTaskDataSummary($task->getSectionId(), $task->getId());
                }
            }
        }

        return $taskData;
    }

    private function hasSectionData(string $sectionId): bool
    {
        $hasData = false;
        foreach ($this->getSections() as $section) {
            if ($sectionId === $section->getId()) {
                foreach ($section->getTasks() as $task) {
                    if (!$this->storage->getTaskData($task)->isEmpty()) {
                        $hasData = true;
                        break;
                    }
                }
            }
        }

        return $hasData;
    }

    public function shouldHideSectionOnReview(string $sectionId): bool
    {
        $shouldHide = false;
        if ('representative' === $sectionId) {
            if (TaskOptions::FLOW_EARLY_CONCILIATION === $this->earlyConcResponse &&
                TaskOptions::REPRESENTATIVE_MYSELF === $this->representingResponse && !$this->hasSectionData($sectionId)) {
                $shouldHide = true;
            }
        }

        return $shouldHide;
    }

    public function getTaskStatus(string $sectionId, string $taskId): string
    {
        $task = $this->getTask($sectionId, $taskId);
        $taskData = $this->storage->getTaskData($task);
        $violations = $this->validator->validate($taskData);

        $isEmpty = $taskData->isEmpty();
        $isValid = 0 === count($violations);

        if (!$isValid && !$isEmpty) {
            return TaskInterface::STATUS_NOT_VALID;
        }

        if ($isValid && !$isEmpty) {
            return TaskInterface::STATUS_COMPLETE;
        }

        if ($isValid) {
            return TaskInterface::STATUS_OPTIONAL;
        }

        return TaskInterface::STATUS_NOT_STARTED;
    }

    public function getTaskValidation(): bool
    {
        $isValid = true;
        foreach ($this->getSections() as $section) {
            foreach ($section->getTasks() as $task) {
                $taskData = $this->storage->getTaskData($task);
                $violations = $this->validator->validate($taskData);
                if (count($violations) > 0) {
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    public function getTaskDataSummary(string $sectionId, string $taskId): string
    {
        $task = $this->getTask($sectionId, $taskId);
        $taskData = $this->storage->getTaskData($task);

        return $this->serializer->serialize($taskData, 'review', ['review' => true, 'taskId' => $task->getId()]);
    }

    public function getTaskTitle(string $sectionId, string $taskId): string
    {
        return $this->getTask($sectionId, $taskId)->getTitle();
    }

    /**
     * @return array<string, string>
     */
    public function getTemplateOptions(string $sectionId, string $taskId): array
    {
        return $this->getTask($sectionId, $taskId)->getTemplateOptions();
    }

    public function getTask(string $sectionId, string $taskId): TaskInterface
    {
        foreach ($this->getSections() as $section) {
            if ($task = $section->getTask($sectionId, $taskId)) {
                return $task;
            }
        }

        throw new \InvalidArgumentException("Task $taskId not found in section $sectionId");
    }

    public function getStorage(): EmploymentDisputeDataPersister
    {
        return $this->storage;
    }
}
