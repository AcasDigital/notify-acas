<?php

namespace App\EmploymentDispute\Wizard;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskLocator;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\EmploymentDispute\Wizard\Steps\WizardStep;
use App\EmploymentDispute\Wizard\Steps\WizardStepInterface;
use App\Translation\TranslatorService;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class WizardCreator
{
    public const STEP_STATUS_COMPLETE = 999999;
    public const STEP_STATUS_START = -111111;
    public const SESSION_WIZARD_NAME = 'wizard';

    /**
     * @var array<WizardStepInterface>
     */
    private array $steps = [];

    public function __construct(private FormFactoryInterface $formFactory, private WizardDataPersister $storage, TranslatorService $translator, TaskLocator $taskLocator)
    {
        $options = $storage->createTaskOptions();

        $this->steps = [
            new WizardStep($taskLocator->getTaskById('wizard_representing')),
            new WizardStep($taskLocator->getTaskById('wizard_relationship'), function () {
                $taskData = $this->storage->getTaskStringData('wizard_representing')->getData();

                return TaskOptions::REPRESENTATIVE_MYSELF === $taskData;
            }),
            new WizardStep($taskLocator->getTaskById('wizard_early_conciliation')),
            new WizardStep($taskLocator->getTaskById('wizard_contact_certificate'), function () {
                $taskData = $this->storage->getTaskStringData('wizard_early_conciliation')->getData();

                return 'yes' === $taskData;
            }),
            new WizardStep($taskLocator->getTaskById('wizard_contact_early_conciliation'), function () {
                $taskData = $this->storage->getTaskStringData('wizard_early_conciliation')->getData();

                return 'no' === $taskData;
            }),
            new WizardStep($taskLocator->getTaskById('wizard_phone'), function () {
                return !$this->hasSelectedContact([TaskOptions::CONTACT_METHOD_PHONE_POST]);
            }),
            new WizardStep($taskLocator->getTaskById('wizard_phone_without_verification'), function () {
                return !$this->hasSelectedContact([TaskOptions::CONTACT_METHOD_PHONE_EMAIL]);
            }),
            new WizardStep($taskLocator->getTaskById('wizard_phone_verify'), function () {
                $phone = $this->storage->getOptionalPhoneData('wizard_phone')->getPhone();
                $isMobile = $phone?->getMobileConfirmation();

                return !$this->hasSelectedContact([TaskOptions::CONTACT_METHOD_PHONE_POST])
                    || !$isMobile || empty($phone->getPhoneNumber());
            }),
            new WizardStep($taskLocator->getTaskById('wizard_address'), function () {
                $myself = TaskOptions::REPRESENTATIVE_MYSELF === $this->storage->getTaskStringData('wizard_representing')->getData();
                $containsPost = $this->hasSelectedContact([TaskOptions::CONTACT_METHOD_POST, TaskOptions::CONTACT_METHOD_PHONE_POST]);

                return !$myself || !$containsPost;
            }),
            new WizardStep($taskLocator->getTaskById('wizard_address_optional'), function () {
                $other = TaskOptions::REPRESENTATIVE_OTHER === $this->storage->getTaskStringData('wizard_representing')->getData();
                $containsPost = $this->hasSelectedContact([TaskOptions::CONTACT_METHOD_POST, TaskOptions::CONTACT_METHOD_PHONE_POST]);

                return !$other || !$containsPost;
            }),
            new WizardStep($taskLocator->getTaskById('wizard_email'), function () {
                return !$this->hasSelectedContact([TaskOptions::CONTACT_METHOD_EMAIL, TaskOptions::CONTACT_METHOD_PHONE_EMAIL]);
            }),
            new WizardStep($taskLocator->getTaskById('wizard_email_verify'), function () {
                $email = $this->storage->getOptionalTaskEmailData('wizard_email')->getData();
                if (null == $email) {
                    return true;
                }

                return !$this->hasSelectedContact([TaskOptions::CONTACT_METHOD_EMAIL, TaskOptions::CONTACT_METHOD_PHONE_EMAIL]);
            }),
            new WizardStep($taskLocator->getTaskById('wizard_memorable_word')),
        ];

        foreach ($this->steps as $step) {
            $step->getTask()->initialize($translator, $options);
        }
    }

    public function startNewForm(): void
    {
        $this->storage->clearData();

        $wizardData = new WizardData();
        $wizardData->setCurrentStep(0);
        $this->storage->setData($wizardData);
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        $wizardData = $this->storage->getData();
        $task = $this->getStepClass($wizardData->getCurrentStep());
        $taskData = $this->storage->getTaskData($task);
        $formBuilder = $this->formFactory->createBuilder(FormType::class, $taskData, [
            'data_class' => $task->getDataClass(),
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);

        return $task->buildForm($formBuilder);
    }

    public function getCurrentTask(): TaskInterface
    {
        $wizardData = $this->storage->getData();

        return $this->getStepClass($wizardData->getCurrentStep());
    }

    public function getTaskOptions(): TaskOptions
    {
        return $this->storage->createTaskOptions();
    }

    public function getTemplate(): string
    {
        $wizardData = $this->storage->getData();
        $step = $this->getStepClass($wizardData->getCurrentStep());

        return $step->getTemplate();
    }

    /**
     * @return array<string, string>
     */
    public function getTemplateOptions(): array
    {
        $wizardData = $this->storage->getData();
        $step = $this->getStepClass($wizardData->getCurrentStep());

        return $step->getTemplateOptions();
    }

    public function getTitle(): string
    {
        $wizardData = $this->storage->getData();
        $step = $this->getStepClass($wizardData->getCurrentStep());

        return $step->getTitle();
    }

    public function isCompleted(): bool
    {
        $wizardData = $this->storage->getData();

        return self::STEP_STATUS_COMPLETE === $wizardData->getCurrentStep();
    }

    public function proceedToStart(): bool
    {
        $wizardData = $this->storage->getData();

        if (null === $wizardData->getCurrentStep()) {
            return true;
        }

        return self::STEP_STATUS_START === $wizardData->getCurrentStep();
    }

    public function proceedPreviousStep(): void
    {
        // clear any previous task data to keep the flow accurate
        // when using the back button.
        $currentTask = $this->getCurrentTask();
        $this->storage->removeTaskData($currentTask);

        $wizardData = $this->storage->getData();
        $stepId = $this->getPreviousStepId($wizardData);
        $wizardData->setCurrentStep($stepId);
        $this->storage->setData($wizardData);
    }

    public function proceedNextStep(): void
    {
        $wizardData = $this->storage->getData();
        $stepId = $this->getNextStepId($wizardData);
        $wizardData->setCurrentStep($stepId);

        $this->storage->setData($wizardData);
    }

    public function saveTaskData(TaskDataInterface $data, TaskInterface $task = null): void
    {
        $wizardData = $this->storage->getData();
        if (!$task) {
            $task = $this->getStepClass($wizardData->getCurrentStep());
        }
        $this->storage->setTaskData($task, $data);
    }

    public function getNextStepId(WizardData $data, ?int $currentStep = null): int
    {
        $step = $currentStep ?? $data->getCurrentStep();
        $nextStep = $step + 1;

        if (count($this->steps) === $nextStep) {
            return self::STEP_STATUS_COMPLETE;
        }

        // Handling browser back and forth navigation breaking the steps.
        // Make the user return to the start page instead of a broken page.
        $checkStep = $this->steps[$nextStep] ?? null;
        if (null === $checkStep) {
            return self::STEP_STATUS_COMPLETE;
        }

        if ($this->steps[$nextStep]->shouldSkip()) {
            return $this->getNextStepId($data, $nextStep);
        }

        return $nextStep;
    }

    public function getPreviousStepId(WizardData $data, ?int $currentStep = null): int
    {
        $step = $currentStep ?? $data->getCurrentStep();
        $prevStep = $step - 1;
        if (0 > $prevStep) {
            return self::STEP_STATUS_START;
        }

        // Handling browser back and forth navigation breaking the steps.
        // Make the user return to the start page instead of a broken page.
        $checkStep = $this->steps[$prevStep] ?? null;
        if (null === $checkStep) {
            return self::STEP_STATUS_COMPLETE;
        }

        if ($this->steps[$prevStep]->shouldSkip()) {
            return $this->getPreviousStepId($data, $prevStep);
        }

        return $prevStep;
    }

    private function getStepClass(int $stepId): TaskInterface
    {
        // Handling browser back and forth navigation breaking the steps.
        // Make the user return to the start page instead of a broken page.
        $checkStep = $this->steps[$stepId] ?? null;
        if (null === $checkStep) {
            throw new \InvalidArgumentException("Step id $stepId, not found in steps.");
        }

        return $this->steps[$stepId]->getTask();
    }

    public function getData(): WizardData
    {
        return $this->storage->getData();
    }

    public function getDataPersister(): WizardDataPersister
    {
        return $this->storage;
    }

    public function saveVerificationCode(?string $code): void
    {
        $this->storage->setVerificationCode($code);
    }

    public function saveVerificationStartDate(?\DateTime $date): void
    {
        $this->storage->setVerificationCodeStartDate($date);
    }

    public function clearConfirmationData(): void
    {
        $this->getData()->setVerificationCode(null);
        $this->getData()->setVerificationCodeStartDate(null);
    }

    /**
     * @param string[] $contactMethods
     */
    public function hasSelectedContact(array $contactMethods): bool
    {
        $contactMethod = $this->storage->getTaskStringData('wizard_contact_certificate')->getData();
        if (empty($contactMethod)) {
            $contactMethod = $this->storage->getTaskStringData('wizard_contact_early_conciliation')->getData();
        }

        if ($contactMethod) {
            return in_array($contactMethod, $contactMethods);
        }

        return false;
    }
}
