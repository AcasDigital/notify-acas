<?php

namespace App\MessageHandler\GovukNofity;

use App\Message\GovukNotify\NotificationMessageInterface;
use App\Message\GovukNotify\ResetMemorableLinkEmailMessage;
use App\Message\GovukNotify\SaveAndReturnEmailMessage;
use App\Message\GovukNotify\SubmissionFailureEmailMessage;
use App\Message\GovukNotify\VerificationEmailMessage;
use App\Message\GovukNotify\VerificationSMSMessage;
use App\Services\GovukNotify\NotificationManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(private NotificationManager $notificationManager)
    {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function __invoke(NotificationMessageInterface $message): void
    {
        if ($message instanceof VerificationEmailMessage) {
            $this->notificationManager->sendEmail($message);
            $this->logger->info("[NOTIFY HANDLER] The {$message->getContactInfo()} email verification queue message has been sent to the queue.");
        } elseif ($message instanceof SaveAndReturnEmailMessage) {
            $this->notificationManager->sendEmail($message);
            $this->logger->info("[NOTIFY HANDLER] The {$message->getContactInfo()} email Save and return details queue message has been sent to the queue.");
        } elseif ($message instanceof VerificationSMSMessage) {
            $this->notificationManager->sendSMS($message);
            $this->logger->info("[NOTIFY HANDLER] The {$message->getContactInfo()} SMS verification queue message has been sent to the queue.");
        } elseif ($message instanceof SubmissionFailureEmailMessage) {
            $this->notificationManager->sendEmail($message);
            $this->logger->info('[NOTIFY HANDLER] Submission failure email sent');
        } elseif ($message instanceof ResetMemorableLinkEmailMessage) {
            $this->notificationManager->sendEmail($message);
            $this->logger->info("[NOTIFY HANDLER] The {$message->getContactInfo()} Reset memorable word link queue message has been sent to the queue.");
        } else {
            $this->logger->error("[NOTIFY HANDLER] FAIELD: The message to {$message->getContactInfo()} has failed. Unknown notification message type.");
            throw new \InvalidArgumentException('Unknown notification message type');
        }
    }
}
