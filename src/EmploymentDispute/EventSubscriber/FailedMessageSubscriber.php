<?php

namespace App\EmploymentDispute\EventSubscriber;

use App\Message\GovukNotify\SubmissionFailureEmailMessage;
use App\Message\SubmissionMessage;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class FailedMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private EmploymentDisputeRepository $disputeFormRepository,
        private string $failureMessageNotificationEmail,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => [
                ['onMessageFailed', 0],
            ],
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if (empty($this->failureMessageNotificationEmail)) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();
        if ($event->willRetry()) {
            return;
        }
        if (!$message instanceof SubmissionMessage) {
            return;
        }

        $id = $message->getDisputeFormId();
        $disputeForm = $this->disputeFormRepository->find($id);
        $submissions = $disputeForm->getEmploymentDisputeSubmissions();
        $lastSubmission = $submissions->last();
        if ($lastSubmission) {
            $personalisation = [
                'case_numbers' => $disputeForm->getCaseNumberList(),
                'error' => $lastSubmission->getResponse(),
            ];

            $message = new SubmissionFailureEmailMessage($this->failureMessageNotificationEmail, $personalisation);

            $this->bus->dispatch($message);
        }
    }
}
