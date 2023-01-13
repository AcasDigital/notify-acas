<?php

namespace App\EmploymentDispute\EventSubscriber;

use App\EmploymentDispute\Event\SaveAndReturnEmailEvent;
use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;
use App\Entity\EmploymentDispute;
use App\Message\GovukNotify\SaveAndReturnEmailMessage;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SaveAndReturnEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus, private EmploymentDisputeRepository $disputeFormRepository, private UrlGeneratorInterface $router)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SaveAndReturnEmailEvent::class => [
                ['sendSaveAndReturnDetails', 0],
            ],
        ];
    }

    public function sendSaveAndReturnDetails(SaveAndReturnEmailEvent $event): void
    {
        $formData = $event->getData();

        if (!$formData instanceof OptionalEmailTaskData) {
            return;
        }

        $id = $event->getId();
        $email = $formData->getData();

        if (!$email) {
            return;
        }

        $disputeForm = $this->disputeFormRepository->find($id);

        if ($disputeForm instanceof EmploymentDispute) {
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
