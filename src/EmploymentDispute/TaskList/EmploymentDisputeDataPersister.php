<?php

namespace App\EmploymentDispute\TaskList;

use App\EmploymentDispute\Tasks\Data\AddressTaskData;
use App\EmploymentDispute\Tasks\Data\ArrayTaskData;
use App\EmploymentDispute\Tasks\Data\ChoiceExtraTaskData;
use App\EmploymentDispute\Tasks\Data\ContactDetailsTaskData;
use App\EmploymentDispute\Tasks\Data\DateTaskData;
use App\EmploymentDispute\Tasks\Data\EmailTaskData;
use App\EmploymentDispute\Tasks\Data\ExtraInformationTaskData;
use App\EmploymentDispute\Tasks\Data\FullNameDataInterface;
use App\EmploymentDispute\Tasks\Data\HolidayDateTaskData;
use App\EmploymentDispute\Tasks\Data\MoneyTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalAddressTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;
use App\EmploymentDispute\Tasks\Data\OrgNameTaskData;
use App\EmploymentDispute\Tasks\Data\PhoneTaskData;
use App\EmploymentDispute\Tasks\Data\PhoneWithoutVerificationTaskData;
use App\EmploymentDispute\Tasks\Data\StringDataInterface;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EmploymentDisputeDataPersister
{
    private ?EmploymentDispute $disputeForm = null;

    public function __construct(
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer,
        private EmploymentDisputeRepository $disputeFormRepository,
    ) {
    }

    public function setEmploymentDispute(EmploymentDispute $disputeForm): self
    {
        $this->disputeForm = $disputeForm;

        return $this;
    }

    public function getEmploymentDispute(): EmploymentDispute
    {
        if (null === $this->disputeForm) {
            throw new \Exception('You need to set the dispute form before operating on a task list.');
        }

        return $this->disputeForm;
    }

    public function getFormData(): EmploymentDisputeData
    {
        $disputeForm = $this->getEmploymentDispute();
        if (empty($disputeForm->getData())) {
            return new EmploymentDisputeData();
        } else {
            return $this->denormalizer->denormalize($disputeForm->getData(), EmploymentDisputeData::class);
        }
    }

    public function createTaskOptions(): TaskOptions
    {
        $data = $this->getEmploymentDispute();
        $options = new TaskOptions();
        if (TaskOptions::REPRESENTATIVE_MYSELF === $data->getRepresenting()) {
            $options->setRepresentative(TaskOptions::REPRESENTATIVE_MYSELF);
        } else {
            $options->setRepresentative(TaskOptions::REPRESENTATIVE_OTHER);
        }

        if (TaskOptions::FLOW_CERTIFICATE === $data->getType()) {
            $options->setFlow(TaskOptions::FLOW_CERTIFICATE);
        } else {
            $options->setFlow(TaskOptions::FLOW_EARLY_CONCILIATION);
        }

        $options->setContactMethods($data->getContactMethod());

        return $options;
    }

    public function setFormData(EmploymentDisputeData $data): void
    {
        $disputeForm = $this->getEmploymentDispute();
        $formData = $this->normalizer->normalize($data);
        assert(is_array($formData));
        $disputeForm->setData($formData);
        $this->disputeFormRepository->add($disputeForm);
    }

    public function getTaskData(TaskInterface $task): TaskDataInterface
    {
        $disputeFormData = $this->getFormData();
        $taskData = $disputeFormData->retrieveTaskData($task->getSectionId(), $task->getDataKey());

        return $this->denormalizer->denormalize($taskData, $task->getDataClass(), null, ['task' => $task]);
    }

    public function setTaskData(string $sectionId, string $dataId, TaskDataInterface $taskData): void
    {
        $disputeFormData = $this->getFormData();
        $denormalizedData = $this->normalizer->normalize($taskData);
        assert(is_array($denormalizedData));
        $disputeFormData->updateTaskData($sectionId, $dataId, $denormalizedData);

        $this->setFormData($disputeFormData);
    }

    public function getTaskStringData(TaskInterface $task): StringDataInterface
    {
        $emptyClass = new ($task->getDataClass())();
        assert($emptyClass instanceof StringDataInterface);
        $data = $this->getTaskData($task);
        if ($data instanceof StringDataInterface) {
            return $data;
        }

        return $emptyClass;
    }

    public function getMoneyTaskData(TaskInterface $task): MoneyTaskData
    {
        assert(MoneyTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof MoneyTaskData) {
            return $data;
        }

        return new MoneyTaskData();
    }

    public function getDateTaskData(TaskInterface $task): DateTaskData
    {
        assert(DateTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof DateTaskData) {
            return $data;
        }

        return new DateTaskData();
    }

    public function getHolidayDateTaskData(TaskInterface $task): HolidayDateTaskData
    {
        assert(HolidayDateTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof HolidayDateTaskData) {
            return $data;
        }

        return new HolidayDateTaskData();
    }

    public function getArrayTaskData(TaskInterface $task): ArrayTaskData
    {
        assert(ArrayTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof ArrayTaskData) {
            return $data;
        }

        return new ArrayTaskData();
    }

    public function getExtraInformationData(TaskInterface $task): ExtraInformationTaskData
    {
        assert(ExtraInformationTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof ExtraInformationTaskData) {
            return $data;
        }

        return new ExtraInformationTaskData();
    }

    public function getChoiceExtraTaskData(TaskInterface $task): ChoiceExtraTaskData
    {
        assert(ChoiceExtraTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof ChoiceExtraTaskData) {
            return $data;
        }

        return new ChoiceExtraTaskData();
    }

    public function getTaskFullNameData(TaskInterface $task): FullNameDataInterface
    {
        $emptyClass = new ($task->getDataClass())();
        assert($emptyClass instanceof FullNameDataInterface);
        $data = $this->getTaskData($task);
        if ($data instanceof FullNameDataInterface) {
            return $data;
        }

        return $emptyClass;
    }

    public function getOrganisationNameData(TaskInterface $task): OrgNameTaskData
    {
        assert(OrgNameTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof OrgNameTaskData) {
            return $data;
        }

        return new OrgNameTaskData();
    }

    public function getTaskContactDetailsData(TaskInterface $task): ContactDetailsTaskData
    {
        assert(ContactDetailsTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof ContactDetailsTaskData) {
            return $data;
        }

        return new ContactDetailsTaskData();
    }

    public function getTaskPhoneData(TaskInterface $task): PhoneTaskData
    {
        assert(PhoneTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof PhoneTaskData) {
            return $data;
        }

        return new PhoneTaskData();
    }

    public function getTaskPhoneWithoutVerificationData(TaskInterface $task): PhoneWithoutVerificationTaskData
    {
        assert(PhoneWithoutVerificationTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof PhoneWithoutVerificationTaskData) {
            return $data;
        }

        return new PhoneWithoutVerificationTaskData();
    }

    public function getTaskEmailData(TaskInterface $task): EmailTaskData|OptionalEmailTaskData
    {
        assert(EmailTaskData::class === $task->getDataClass() || OptionalEmailTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof EmailTaskData) {
            return $data;
        }

        return new EmailTaskData();
    }

    public function getTaskAddressData(TaskInterface $task): AddressTaskData
    {
        assert(AddressTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof AddressTaskData) {
            return $data;
        }

        return new AddressTaskData();
    }

    public function getOptionalTaskAddressData(TaskInterface $task): OptionalAddressTaskData
    {
        assert(OptionalAddressTaskData::class === $task->getDataClass());
        $data = $this->getTaskData($task);
        if ($data instanceof OptionalAddressTaskData) {
            return $data;
        }

        return new OptionalAddressTaskData();
    }
}
