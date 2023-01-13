<?php

namespace App\Job;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.job'])]
interface JobInterface
{
    public function run(): string;

    public function shouldRun(): bool;

    public function getNextRunTime(): \DateTime;

    public function getLabel(): string;

    public function getLastRunTime(): ?\DateTimeInterface;

    public function getStatus(): string;

    public function isHealthy(): bool;

    public function isRetryable(): bool;
}
