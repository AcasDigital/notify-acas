<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskOptions;

class RespondentSection extends BaseSection implements SectionInterface, SectionSwitcherInterface, RepeatedSectionInterface
{
    public const TASK_RESPONDENT = 'respondent';
    public const TASK_ORG_NAME = 'respondent_org_name';
    public const TASK_ORG_ADDRESS = 'respondent_org_address';
    public const TASK_ORG_IN_BUSINESS = 'respondent_org_in_business';
    public const TASK_PERSON_NAME = 'respondent_person_name';
    public const TASK_PERSON_ADDRESS = 'respondent_person_address';

    private mixed $switcherData;
    private string $id;

    public function initializeTasks(TaskOptions $taskOptions): void
    {
        $tasks[self::TASK_RESPONDENT] = $this->getSwitcherTask();

        if ('organisation' === $this->switcherData) {
            $tasks[self::TASK_ORG_NAME] = $this->taskLocator->getTaskById(self::TASK_ORG_NAME);
            $tasks[self::TASK_ORG_ADDRESS] = $this->taskLocator->getTaskById(self::TASK_ORG_ADDRESS);
            $tasks[self::TASK_ORG_IN_BUSINESS] = $this->taskLocator->getTaskById(self::TASK_ORG_IN_BUSINESS);
        } elseif ('person' === $this->switcherData) {
            $tasks[self::TASK_PERSON_NAME] = $this->taskLocator->getTaskById(self::TASK_PERSON_NAME);
            $tasks[self::TASK_PERSON_ADDRESS] = $this->taskLocator->getTaskById(self::TASK_PERSON_ADDRESS);
        }

        $this->tasks = $tasks;

        foreach ($this->tasks as $task) {
            $task->initialize($this->translator, $taskOptions);
        }
    }

    public function getSwitcherTask(): TaskInterface
    {
        return $this->taskLocator->getTaskById(self::TASK_RESPONDENT);
    }

    public function setSwitcherTaskData(mixed $data): void
    {
        if (is_array($data)) {
            $this->switcherData = $data['data'] ?? null;
        } else {
            $this->switcherData = null;
        }
    }

    public function getType(): string
    {
        return 'respondent';
    }

    public function getId(): string
    {
        return $this->id ?? $this->generateId();
    }

    public function generateId(): string
    {
        return 'respondent-'.uniqid();
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): string
    {
        // return $this->translator->getText('respondent_section.task_list.section_label');
        return 'Details of who the claim is against';
    }

    public function getReviewPageLabel(): string
    {
        // return $this->translator->getText('respondent_section.review.section_label');
        return 'Organisations or persons the claim is against';
    }

    public function getSwitcherTaskData(): mixed
    {
        return $this->switcherData;
    }

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void
    {
        $tasks = $this->getTasks();
        if ('person' === $this->getSwitcherTaskData()) {
            $fullName = $address = null;
            if (isset($tasks[self::TASK_PERSON_NAME])) {
                $fullName = $storage->getTaskFullNameData($tasks[self::TASK_PERSON_NAME])->getFullName();
            }

            if (isset($tasks[self::TASK_PERSON_ADDRESS])) {
                $address = $storage->getTaskAddressData($tasks[self::TASK_PERSON_ADDRESS])->getAddress();
            }

            $payload->addRespondentPerson($fullName, $address);
        } else {
            $orgName = $address = null;
            if (isset($tasks[self::TASK_ORG_ADDRESS])) {
                $address = $storage->getTaskAddressData($tasks[self::TASK_ORG_ADDRESS])->getAddress();
            }

            $insolvent = false;
            if (isset($tasks[self::TASK_ORG_IN_BUSINESS])) {
                $data = $storage->getTaskStringData($tasks[self::TASK_ORG_IN_BUSINESS])->getData();
                if ('no' === $data) {
                    $insolvent = true;
                }
            }
            $payload->setOrganisationOutOfBusiness($insolvent);

            if (isset($tasks[self::TASK_ORG_NAME])) {
                $organisation = $storage->getOrganisationNameData($tasks[self::TASK_ORG_NAME]);
                $orgName = $organisation->getOrgName();
                $payload->addOrganisationInfo($organisation, $insolvent);
            }

            $payload->addRespondentOrganisation($orgName, $address);
        }
    }
}
