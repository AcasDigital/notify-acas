<?php

namespace App\HealthCheck;

use App\Job\JobInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class BackgroundJobHealthCheck implements HealthCheckInterface
{
    /**
     * @var iterable<JobInterface>
     */
    private iterable $jobs;

    /**
     * @param iterable<JobInterface> $jobs
     */
    public function __construct(
        #[TaggedIterator('app.job')] iterable $jobs,
        ) {
        $this->jobs = $jobs;
    }

    public function check(): HealthCheckResponse
    {
        $response = new HealthCheckResponse();
        $response->setLabel('Background Jobs');
        foreach ($this->jobs as $job) {
            if (!$job->isHealthy()) {
                $response->addError($job->getLabel().' is reporting unhealthy');
            }
        }

        return $response;
    }
}
