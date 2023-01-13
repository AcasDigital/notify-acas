<?php

namespace App\EmploymentDispute\EventSubscriber;

use App\EmploymentDispute\Event\ResetMemorableLinkEvent;
use App\Entity\EmploymentDispute;
use App\Message\GovukNotify\ResetMemorableLinkEmailMessage;
use App\Repository\EmploymentDisputeRepository;
use App\Services\SecurityHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetMemorableLinkSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $resetLinkTimestamp,
        private MessageBusInterface $bus,
        private EmploymentDisputeRepository $disputeFormRepository,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResetMemorableLinkEvent::class => [
                ['sendResetMemorableWordResetLink', 0],
            ],
        ];
    }

    public function sendResetMemorableWordResetLink(ResetMemorableLinkEvent $event): void
    {
        $email = $event->getEmail();
        $id = $event->getId();

        $disputeForm = $this->disputeFormRepository->find($id);

        if (!$disputeForm instanceof EmploymentDispute) {
            $this->logger->error("[RESETLINKEVENTLISTENER] - form $id doesn't exist");

            return;
        }

        $memorableWord = $disputeForm->getMemorableWord();

        if (!$memorableWord) {
            $this->logger->error("[RESETLINKEVENTLISTENER] - memorable word for form $id is null and can't be re-set");

            return;
        }

        $message = new ResetMemorableLinkEmailMessage($email, ['reset_memorable_word_link' => $this->generateResetUrl($id, $email, $memorableWord)]);

        $this->bus->dispatch($message);
    }

    private function generateResetUrl(string $id, string $email, string $memorableWord): string
    {
        $date = new \DateTime();
        $expiryMinutes = $this->resetLinkTimestamp;
        $expiryDate = $date->add(\DateInterval::createFromDateString($expiryMinutes.'mins'));
        $timestamp = $expiryDate->getTimestamp();

        return $this->router->generate('reset_memorable_word', ['id' => $id, 'token' => SecurityHelper::generateUrlAccessHash($email, $memorableWord, $timestamp), 'timestamp' => $timestamp], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
