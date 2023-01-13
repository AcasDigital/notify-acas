<?php

namespace App\MessageHandler;

use App\Entity\JobHistory;
use App\Job\JobInterface;
use App\Message\RunJobMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RunJobMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var iterable<JobInterface>
     */
    private $jobs;

    /**
     * @param iterable<JobInterface> $jobs
     */
    public function __construct(
        #[TaggedIterator('app.job', 'getId')] iterable $jobs,
        private EntityManagerInterface $em,
        private LockFactory $lock
    ) {
        $this->jobs = $jobs;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function __invoke(RunJobMessage $message): void
    {
        $type = $message->getId();
        $job = null;
        foreach ($this->jobs as $key => $value) {
            if ($key === $type) {
                $job = $value;
                break;
            }
        }
        if (!$job) {
            $this->logger->error("[JOBRUN] No job found with ID $type. Discarding message.");

            return;
        }

        $lock = $this->lock->createLock($type, 100.0, false);
        if (!$lock->acquire()) {
            $this->logger->notice("[JOBRUN] Lock already exists for $type");

            return;
        }
        $this->logger->notice("[JOBRUN] Lock acquired for $type");

        if (!$job->shouldRun()) {
            $this->logger->notice("[JOBRUN] $type is not scheduled to run yet.");
        }
        $this->logger->notice("[JOBRUN] Running $type ");

        $jobHistory = new JobHistory();
        $jobHistory->setStarted(new \DateTime());
        $jobHistory->setType($type);

        try {
            $result = $job->run();
            $jobHistory->setData($result);

            $jobHistory->setStatus($job->getStatus());
        } catch (\Throwable $e) {
            $jobHistory->setStatus(JobHistory::STATUS_FAILED);
            $jobHistory->setData($e->getMessage());
            if ($job->isRetryable()) {
                throw $e;
            }
        } finally {
            $jobHistory->setFinished(new \DateTime());
            $this->em->persist($jobHistory);
            $this->em->flush();
            $lock->release();
            $this->logger->notice('[JOBRUN] Lock released for '.$message->getId());
        }
    }
}
