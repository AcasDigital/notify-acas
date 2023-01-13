<?php

namespace App\MessageHandler;

use App\EmploymentDispute\Submission\CRMSubmitter;
use App\Entity\EmploymentDispute;
use App\Message\SubmissionMessage;
use App\Repository\EmploymentDisputeRepository;
use App\Services\SettingManager;
use App\Services\UsageReportCreator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SubmissionMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        private EmploymentDisputeRepository $employmentDisputeRepository,
        private CRMSubmitter $submitter,
        private MessageBusInterface $bus,
        private SettingManager $settingManager,
        private UsageReportCreator $usageReportCreator,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function __invoke(SubmissionMessage $message): void
    {
        $id = $message->getDisputeFormId();
        $employmentDispute = $this->employmentDisputeRepository->find($id);

        if (is_null($employmentDispute)) {
            throw new UnrecoverableMessageHandlingException('No employment dispute found for id: '.$id);
        }

        if (!$this->isProcessable($employmentDispute)) {
            $this->logger->info(sprintf("[SUBMISSION] Employment dispute has non-queued status: '%s'. Removing from queue.", $employmentDispute->getStatus()));

            return;
        }

        if ($this->settingManager->getBool(SettingManager::SUBMISSION_PAUSED, false) && !$message->isForcedSubmission()) {
            if (EmploymentDispute::STATUS_PAUSED !== $employmentDispute->getStatus()) {
                $employmentDispute->setStatus(EmploymentDispute::STATUS_PAUSED);
                $this->employmentDisputeRepository->add($employmentDispute);
            }

            $this->logger->info("[SUBMISSION] Message Handler paused. Delaying submission id $id. Case number: ".$employmentDispute->getCaseNumberList());

            $this->bus->dispatch($message, [DelayStamp::delayFor(\DateInterval::createFromDateString('+5 minutes'))]);

            return;
        }

        if ($message->isForcedSubmission()) {
            $this->logger->info("[SUBMISSION] Forced submission of $id. Case number: ".$employmentDispute->getCaseNumberList());
        } else {
            $this->logger->info("[SUBMISSION] Submitting $id. Case number: ".$employmentDispute->getCaseNumberList());
        }
        try {
            $this->submitter->submit($employmentDispute, $message->getCustomPayload());
            $employmentDispute->setStatus(EmploymentDispute::STATUS_SUBMITTED);
            $this->employmentDisputeRepository->add($employmentDispute);
            $this->logger->info("[SUBMISSION] Successfully submitted $id. Case number: ".$employmentDispute->getCaseNumberList());
        } catch (\Throwable $e) {
            $employmentDispute->setStatus(EmploymentDispute::STATUS_FAILED);
            $this->employmentDisputeRepository->add($employmentDispute);
            $this->logger->error(sprintf('[SUBMISSION] Failed to submit %s. Case number: %s. Message: %s', $id, $employmentDispute->getCaseNumberList(), $e->__toString()));

            throw $e;
        }

        try {
            if (EmploymentDispute::STATUS_SUBMITTED === $employmentDispute->getStatus()) {
                $this->usageReportCreator->createAndPersistFromDispute($employmentDispute);
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[SUBMISSION] [USAGESTATS] Failed create stats entity for %s. Case number: %s. Message: %s', $id, $employmentDispute->getCaseNumberList(), $e->__toString()));
        }
    }

    private function isProcessable(EmploymentDispute $employmentDispute): bool
    {
        return in_array($employmentDispute->getStatus(), [EmploymentDispute::STATUS_QUEUED, EmploymentDispute::STATUS_PAUSED, EmploymentDispute::STATUS_FAILED]);
    }
}
