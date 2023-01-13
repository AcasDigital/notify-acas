<?php

namespace App\EmploymentDispute\Wizard;

use App\EmploymentDispute\Tasks\Data\AddressTaskData;
use App\EmploymentDispute\Tasks\Data\EmailTaskData;
use App\EmploymentDispute\Tasks\Data\MemorableWordTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalAddressTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalPhoneTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalStringTaskData;
use App\EmploymentDispute\Tasks\Data\PhoneTaskData;
use App\EmploymentDispute\Tasks\Data\PhoneWithoutVerificationTaskData;
use App\EmploymentDispute\Tasks\Data\StringDataInterface;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskLocator;
use App\EmploymentDispute\Tasks\TaskOptions;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WizardDataPersister
{
    private SessionInterface $session;

    public function __construct(
        RequestStack $requestStack,
        private TaskLocator $taskLocator,
        private DenormalizerInterface $denormalizer,
        private NormalizerInterface $normalizer,
        private SerializerInterface $serializer)
    {
        $this->session = $requestStack->getSession();
    }

    public function getTaskData(TaskInterface $task): TaskDataInterface
    {
        $wizardData = $this->getData();

        return $this->denormalizer->denormalize($wizardData->getTaskData($task->getDataKey()), $task->getDataClass());
    }

    public function getTaskStringData(string $taskId): StringDataInterface
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $emptyClass = new ($task->getDataClass())();
        assert($emptyClass instanceof StringDataInterface);
        $data = $this->getTaskData($task);
        if ($data instanceof StringDataInterface) {
            return $data;
        }

        return $emptyClass;
    }

    public function getOptionalTaskStringData(string $taskId): OptionalStringTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof OptionalStringTaskData) {
            return $data;
        }

        return new OptionalStringTaskData();
    }

    public function getTaskPhoneData(string $taskId): PhoneTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof PhoneTaskData) {
            return $data;
        }

        return new PhoneTaskData();
    }

    public function getOptionalPhoneData(string $taskId): OptionalPhoneTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof OptionalPhoneTaskData) {
            return $data;
        }

        return new OptionalPhoneTaskData();
    }

    public function getPhoneWithoutVerificationPhoneData(string $taskId): PhoneWithoutVerificationTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof PhoneWithoutVerificationTaskData) {
            return $data;
        }

        return new PhoneWithoutVerificationTaskData();
    }

    public function getTaskEmailData(string $taskId): EmailTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof EmailTaskData) {
            return $data;
        }

        return new EmailTaskData();
    }

    public function getOptionalTaskEmailData(string $taskId): OptionalEmailTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof OptionalEmailTaskData) {
            return $data;
        }

        return new OptionalEmailTaskData();
    }

    public function getMemorableWordData(string $taskId): MemorableWordTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof MemorableWordTaskData) {
            return $data;
        }

        return new MemorableWordTaskData();
    }

    public function getTaskAddressData(string $taskId): AddressTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof AddressTaskData) {
            return $data;
        }

        return new AddressTaskData();
    }

    public function getOptionalTaskAddressData(string $taskId): OptionalAddressTaskData
    {
        $task = $this->taskLocator->getTaskById($taskId);
        $data = $this->getTaskData($task);
        if ($data instanceof OptionalAddressTaskData) {
            return $data;
        }

        return new OptionalAddressTaskData();
    }

    public function setTaskData(TaskInterface $task, TaskDataInterface $data): void
    {
        $wizardData = $this->getData();

        $data = $this->normalizer->normalize($data);
        assert(is_array($data));
        $wizardData->setTaskData($task->getDataKey(), $data);
        $this->setData($wizardData);
    }

    public function removeTaskData(TaskInterface $task): void
    {
        $wizardData = $this->getData();
        $wizardData->removeTaskData($task->getDataKey());
        $this->setData($wizardData);
    }

    public function createTaskOptions(): TaskOptions
    {
        $options = new TaskOptions();
        if (TaskOptions::REPRESENTATIVE_MYSELF === $this->getTaskStringData('wizard_representing')->getData()) {
            $options->setRepresentative(TaskOptions::REPRESENTATIVE_MYSELF);
        } else {
            $options->setRepresentative(TaskOptions::REPRESENTATIVE_OTHER);
        }

        $contactMethod = $this->getTaskStringData('wizard_contact_certificate')->getData();
        if (empty($contactMethod)) {
            $contactMethod = $this->getTaskStringData('wizard_contact_early_conciliation')->getData();
        }
        $options->setContactMethods($contactMethod);

        $options->setFlow('yes' === $this->getTaskStringData('wizard_early_conciliation')->getData() ? TaskOptions::FLOW_EARLY_CONCILIATION : TaskOptions::FLOW_CERTIFICATE);

        return $options;
    }

    public function setVerificationCode(?string $code): void
    {
        $wizardData = $this->getData();
        $wizardData->setVerificationCode($code);
        $this->setData($wizardData);
    }

    public function setVerificationCodeStartDate(?\DateTime $date): void
    {
        $wizardData = $this->getData();
        $wizardData->setVerificationCodeStartDate($date);
        $this->setData($wizardData);
    }

    public function setStatus(string $status): void
    {
        $wizardData = $this->getData();
        $wizardData->setStatus($status);
        $this->setData($wizardData);
    }

    public function getData(): WizardData
    {
        $json = $this->session->get(WizardCreator::SESSION_WIZARD_NAME, '{}');
        if (!is_string($json)) {
            $json = '{}';
        }

        return $this->serializer->deserialize($json, WizardData::class, 'json');
    }

    public function setData(WizardData $data): void
    {
        $json = $this->serializer->serialize($data, 'json');
        $this->session->set(WizardCreator::SESSION_WIZARD_NAME, $json);
    }

    public function clearData(): void
    {
        $this->session->remove(WizardCreator::SESSION_WIZARD_NAME);
    }
}
