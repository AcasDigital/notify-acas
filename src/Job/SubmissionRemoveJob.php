<?php

namespace App\Job;

use App\Repository\EmploymentDisputeRepository;
use App\Repository\JobHistoryRepository;
use App\Services\SettingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1)]
class SubmissionRemoveJob extends AbstractJob
{
    public const CRON_EXPRESSION = '@daily';

    public function __construct(
        JobHistoryRepository $repository,
        private EntityManagerInterface $em,
        private SettingManager $settingManager,
        private EmploymentDisputeRepository $employmentDisputeRepository,
    ) {
        parent::__construct($repository);
    }

    public function getLabel(): string
    {
        return 'Remove submissions';
    }

    public function getDescription(): string
    {
        return 'Removes submissions after '.$this->settingManager->getInt(SettingManager::SUBMISSION_CLEANUP, 0).' days';
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
        $setDaysAgo = (new \DateTime())->modify('-'.$this->settingManager->getInt(SettingManager::SUBMISSION_CLEANUP, 0).' days');
        $toBeDeleted = $this->employmentDisputeRepository->findSubmittedBeforeDate($setDaysAgo);
        foreach ($toBeDeleted as $tbd) {
            $this->em->remove($tbd);
        }

        $messages[] = sprintf('Removed %s submitted forms', count($toBeDeleted));

        // This is a special implementation
        return implode(PHP_EOL, $messages);
    }
}
