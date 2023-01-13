<?php

namespace App\EmploymentDispute\EventSubscriber;

use App\EmploymentDispute\Event\FormPreparedEvent;
use App\EmploymentDispute\Tasks\Data\MemorableWordTaskData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\EmploymentDispute;
use App\Message\GovukNotify\SaveAndReturnEmailMessage;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormPreparedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private EmploymentDisputeRepository $disputeFormRepository,
        private UrlGeneratorInterface $router
        ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormPreparedEvent::class => [
                ['sendSaveAndReturnDetails', 0],
            ],
        ];
    }

    public function sendSaveAndReturnDetails(FormPreparedEvent $event): void
    {
        $formData = $event->getData();

        if (!$formData instanceof MemorableWordTaskData) {
            return;
        }

        $id = $event->getId();
        $memorableWord = $formData->getData();

        if (!$memorableWord) {
            return;
        }

        $email = null;

        $disputeForm = $this->disputeFormRepository->find($id);

        if ($disputeForm instanceof EmploymentDispute) {
            $email = $this->getNotificationEmail($disputeForm);

            if ($email) {
                $personalisation = [
                    'sr_number' => $disputeForm->getId(),
                    'return_to_form_page_link' => $this->router->generate('return_from_start', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'mem_word_reset_page_link' => $this->router->generate('reset_memorable_from_start', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ];

                $message = new SaveAndReturnEmailMessage($email, $personalisation);

                $this->bus->dispatch($message);
            }
        }
    }

    private function getNotificationEmail(EmploymentDispute $disputeForm): ?string
    {
        $verificationEmail = null;

        if (TaskOptions::CONTACT_METHOD_EMAIL === $disputeForm->getContactMethod() || TaskOptions::CONTACT_METHOD_PHONE_EMAIL === $disputeForm->getContactMethod()) {
            $verificationEmail = $disputeForm->getVerificationContact();
        }

        return $verificationEmail ?: null;
    }
}
