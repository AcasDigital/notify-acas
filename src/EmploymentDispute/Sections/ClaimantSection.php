<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\TaskOptions;

class ClaimantSection extends BaseSection implements SectionInterface
{
    public const TASK_NAME = 'claimant_name';
    public const TASK_PHONE = 'claimant_phone';
    public const TASK_EMAIL = 'claimant_email_optional';
    public const TASK_ADDRESS = 'claimant_address';

    public function initializeTasks(TaskOptions $taskOptions): void
    {
        $taskIds = [];

        if (TaskOptions::FLOW_EARLY_CONCILIATION === $taskOptions->getFlow() && TaskOptions::REPRESENTATIVE_MYSELF === $taskOptions->getRepresentative()) {
            $taskIds[] = self::TASK_NAME;
            if ($taskOptions->hasPhone()) {
                $taskIds[] = self::TASK_PHONE;
            }
            if ($taskOptions->hasEmail()) {
                $taskIds[] = self::TASK_EMAIL;
            }

            $taskIds[] = self::TASK_ADDRESS;
        } elseif (TaskOptions::FLOW_CERTIFICATE === $taskOptions->getFlow() && TaskOptions::REPRESENTATIVE_MYSELF === $taskOptions->getRepresentative()) {
            if ($taskOptions->hasEmail()) {
                $taskIds = [
                    self::TASK_EMAIL,
                    self::TASK_NAME,
                    self::TASK_ADDRESS,
                ];
            } elseif ($taskOptions->hasPost()) {
                // certification and post choice should not have the email option listed for legal reasons
                $taskIds = [
                    self::TASK_NAME,
                    self::TASK_ADDRESS,
                ];
            }
        } else {
            $taskIds = [
                self::TASK_NAME,
                self::TASK_ADDRESS,
            ];
        }

        foreach ($taskIds as $id) {
            $task = $this->taskLocator->getTaskById($id);
            $task->initialize($this->translator, $taskOptions);
            $this->tasks[$id] = $task;
        }
    }

    public function getId(): string
    {
        return 'claimant';
    }

    public function getLabel(): string
    {
        return TaskOptions::REPRESENTATIVE_MYSELF === $this->taskOptions->getRepresentative() ? 'Your details' : 'Claimant\'s details';
    }

    public function getReviewPageLabel(): string
    {
        return TaskOptions::REPRESENTATIVE_MYSELF === $this->taskOptions->getRepresentative() ? 'Your personal details' : 'Claimant details';
    }

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void
    {
        $tasks = $this->getTasks();
        if (isset($tasks[self::TASK_NAME])) {
            $payload->setClaimantName($storage->getTaskFullNameData($tasks[self::TASK_NAME])->getFullName());
        }

        if (isset($tasks[self::TASK_PHONE])) {
            $phone = $storage->getTaskPhoneWithoutVerificationData($tasks[self::TASK_PHONE])->getPhone();
            $payload->setClaimantPhoneNumber($phone);

            if ($phone?->getExtraInformation()) {
                $payload->setDescription($phone->getExtraInformation());
            }
        }

        if (isset($tasks[self::TASK_EMAIL])) {
            $email = $storage->getTaskStringData($tasks[self::TASK_EMAIL])->getData();
            $payload->setClaimantEmail($email);
        }

        if (isset($tasks[self::TASK_ADDRESS])) {
            $address = $storage->getTaskAddressData($tasks[self::TASK_ADDRESS])->getAddress();
            $payload->setClaimantAddress($address);
        }
    }
}
