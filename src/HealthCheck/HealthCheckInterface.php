<?php

namespace App\HealthCheck;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.healthcheck'])]
interface HealthCheckInterface
{
    public function check(): HealthCheckResponse;
}
