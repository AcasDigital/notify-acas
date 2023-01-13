<?php

namespace App\HealthCheck;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class HealthChecker
{
    /**
     * @var iterable<HealthCheckInterface>
     */
    private iterable $healthchecks;

    /**
     * @param iterable<HealthCheckInterface> $healthchecks
     */
    public function __construct(
        #[TaggedIterator('app.healthcheck')] iterable $healthchecks,
        ) {
        $this->healthchecks = $healthchecks;
    }

    /**
     * @return HealthCheckResponse[]
     */
    public function runChecks(): array
    {
        $responses = [];
        foreach ($this->healthchecks as $healthcheck) {
            $responses[] = $healthcheck->check();
        }

        return $responses;
    }
}
