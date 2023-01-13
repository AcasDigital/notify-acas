<?php

namespace App\Services\GovukNotify;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException;
use App\Message\GovukNotify\NotificationMessageInterface;
use Psr\Log\LoggerInterface;

class NotificationManager
{
    private NotifyClient $notifyClientForEmailFlow;
    private NotifyClient $notifyClientForSMSFlow;

    public function __construct(
        private string $notifyEmailVerificationFlowApiKey,
        private string $notifySMSVerificationFlowApiKey,
        private LoggerInterface $logger
        ) {
        $this->notifyClientForEmailFlow = new \Alphagov\Notifications\Client([
            'apiKey' => $this->notifyEmailVerificationFlowApiKey,
            'httpClient' => new \Http\Adapter\Guzzle7\Client(),
        ]);
        $this->notifyClientForSMSFlow = new \Alphagov\Notifications\Client([
            'apiKey' => $this->notifySMSVerificationFlowApiKey,
            'httpClient' => new \Http\Adapter\Guzzle7\Client(),
        ]);
    }

    public function sendEmail(NotificationMessageInterface $message): void
    {
        try {
            $response = $this->notifyClientForEmailFlow->sendEmail($message->getContactInfo(), $message->getTemplate(), $message->getPersonalisation());
            $this->logger->info("[NOTIFY] Sending email to {$message->getContactInfo()}");
            $this->logger->debug('[NOTIFY] Email response received from Govuk Notify', ['response' => $response]);
        } catch (ApiException $e) {
            $this->logger->error(sprintf('[NOTIFY] the email could not be sent to %s. Reason: %s', $message->getContactInfo(), $e->getMessage()));
        }
    }

    public function sendSMS(NotificationMessageInterface $message): void
    {
        try {
            $response = $this->notifyClientForSMSFlow->sendSMS($message->getContactInfo(), $message->getTemplate(), $message->getPersonalisation());
            $this->logger->info("[NOTIFY] SMS - Sending verification SMS to {$message->getContactInfo()}");
            $this->logger->debug('[NOTIFY] SMS - Verification response received from Govuk Notify', ['response' => $response]);
        } catch (ApiException $e) {
            $this->logger->error(sprintf('[NOTIFY] the SMS could not be sent to %s. Reason: %s', $message->getContactInfo(), $e->getMessage()));
        }
    }
}
