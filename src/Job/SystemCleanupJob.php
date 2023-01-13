<?php

namespace App\Job;

use App\Repository\JobHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1)]
class SystemCleanupJob extends AbstractJob
{
    public const CRON_EXPRESSION = '@daily';

    public function __construct(
        JobHistoryRepository $repository,
        private EntityManagerInterface $em)
    {
        parent::__construct($repository);
    }

    public function getLabel(): string
    {
        return 'System Cleanup';
    }

    public function getDescription(): string
    {
        return 'Cleans up logs more than a week old.';
    }

    protected function getCronString(): string
    {
        return '@daily';
    }

    public function getNextRunTime(): \DateTime
    {
        $lastStarted = $this->getLastRunTime();
        if ($lastStarted) {
            $lastStarted = \DateTime::createFromInterface($lastStarted);
            $nextRunTime = (clone $lastStarted)->modify('tomorrow midnight');
        } else {
            $lastStarted = new \DateTime();
            $nextRunTime = \DateTime::createFromFormat('Y-m-d H:i:00', $lastStarted->format('Y-m-d H:i:00'));
        }

        assert($nextRunTime instanceof \DateTime);

        return $nextRunTime;
    }

    public function run(): string
    {
        $sevenDaysAgo = (new \DateTime())->modify('-7 days');
        $query = $this->em->createQuery('DELETE FROM App\Entity\JobHistory h WHERE h.started < :date')->setParameter('date', $sevenDaysAgo);
        $result = $query->execute();
        $messages[] = sprintf('Cleaned up %s job history messages', $result);

        // This is a special implementation
        return implode(PHP_EOL, $messages);
    }
}
