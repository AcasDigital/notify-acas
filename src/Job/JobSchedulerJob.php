<?php

namespace App\Job;

use App\Repository\JobHistoryRepository;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 2)]
class JobSchedulerJob extends AbstractJob
{
    public function __construct(JobHistoryRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getLabel(): string
    {
        return 'Job Scheduler';
    }

    public function getDescription(): string
    {
        return 'This job schedules all other jobs on the system. If this job is unhealthy all other jobs will not run.';
    }

    protected function getCronString(): string
    {
        return '* * * * *';
    }

    public function run(): string
    {
        // This is a special implementation
        return 'Job run';
    }
}
