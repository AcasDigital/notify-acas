<?php

namespace App\EmploymentDispute\Wizard\EventListener;

use App\EmploymentDispute\Tasks\Data\ConfirmationCodeResendTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalPhoneTaskData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\EmploymentDispute\Wizard\Event\StepCompletedEvent;
use App\EmploymentDispute\Wizard\WizardCreator;
use App\Message\GovukNotify\VerificationEmailMessage;
use App\Message\GovukNotify\VerificationSMSMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class VerificationSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $codeOverride, private MessageBusInterface $bus, private WizardCreator $wizard)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepCompletedEvent::class => [
                ['sendVerification', 0],
            ],
        ];
    }

    public function sendVerification(StepCompletedEvent $event): void
    {
        $formData = $event->getData();
        if ($formData instanceof OptionalEmailTaskData) {
            $this->verifyEmail();
        }

        if ($formData instanceof OptionalPhoneTaskData) {
            $this->verifyPhone();
        }

        if ($formData instanceof ConfirmationCodeResendTaskData) {
            if ($this->wizard->hasSelectedContact([TaskOptions::CONTACT_METHOD_PHONE_POST])) {
                $this->verifyPhone();
            } else {
                $this->verifyEmail();
            }
        }
    }

    private function verifyEmail(): void
    {
        $code = $this->generateAndSaveVerificationCode();
        $wizardData = $this->wizard->getDataPersister();
        $email = $wizardData->getOptionalTaskEmailData('wizard_email')->getData();
        if ($email) {
            $message = new VerificationEmailMessage($email, ['code' => $code]);

            $this->bus->dispatch($message);
        }
    }

    private function verifyPhone(): void
    {
        $wizardData = $this->wizard->getDataPersister();

        if ($this->wizard->hasSelectedContact([TaskOptions::CONTACT_METHOD_PHONE_POST])) {
            $phoneData = $wizardData->getOptionalPhoneData('wizard_phone')->getPhone();
            $phone = $phoneData?->getPhoneNumber();
            $isMobile = $phoneData?->getMobileConfirmation();
            if ($phone && $isMobile) {
                $code = $this->generateAndSaveVerificationCode();
                $message = new VerificationSMSMessage($phone, ['code' => $code]);

                $this->bus->dispatch($message);
            }
        }
    }

    private function generateAndSaveVerificationCode(): string
    {
        $code = (string) rand(10000, 99999);

        // Override option from the environment variables for easier testing.
        if ($this->codeOverride) {
            $code = $this->codeOverride;
        }

        $this->wizard->saveVerificationCode($code);
        $this->wizard->saveVerificationStartDate(new \DateTime());

        return $code;
    }
}
