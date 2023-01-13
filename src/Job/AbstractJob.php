<?php

namespace App\Job;

use App\Entity\JobHistory;
use App\Repository\JobHistoryRepository;
use Cron\CronExpression;

abstract class AbstractJob implements JobInterface
{
    private string $status;

    public function __construct(private JobHistoryRepository $repository)
    {
        $this->status = 'success';
    }

    public function markFailed(): void
    {
        $this->status = 'failed';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function shouldRun(): bool
    {
        $now = new \DateTime();
        $nextRunTime = $this->getNextRunTime();

        return $now >= $nextRunTime;
    }

    public function isHealthy(): bool
    {
        if (!$this->getLastRunTime()) {
            return false;
        }

        if ($this->shouldRun()) {
            $now = new \DateTime();

            // Give the job a 5 minute grace period before marking it as unhealthy.
            $nextRunTime = $this->getNextRunTime();
            $nextRunTime->modify('+5 minutes');

            if ($nextRunTime <= $now) {
                return false;
            }
        }

        $lastRun = $this->getLastRun();
        if (!$lastRun || JobHistory::STATUS_SUCCESS !== $lastRun->getStatus()) {
            return false;
        }

        return true;
    }

    public function getLastRun(): ?JobHistory
    {
        $lastRun = $this->repository->findOneBy(['type' => get_class($this)], ['started' => 'DESC']);

        return $lastRun;
    }

    public function getLastRunTime(): ?\DateTimeInterface
    {
        $lastRun = $this->getLastRun();

        return $lastRun?->getStarted();
    }

    /**
     * Default schedule is to run every minute.
     */
    public function getNextRunTime(): \DateTime
    {
        $cron = new CronExpression($this->getCronString());

        return $cron->getNextRunDate($this->getLastRunTime() ?? 'now');
    }

    public function isRetryable(): bool
    {
        return false;
    }

    protected function getCronString(): string
    {
        return '@daily';
    }
}
