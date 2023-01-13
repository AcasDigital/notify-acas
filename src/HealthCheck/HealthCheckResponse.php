<?php

namespace App\HealthCheck;

class HealthCheckResponse
{
    /**
     * @var string[]
     */
    private array $errors = [];
    private string $label = 'Healthcheck';

    public function isHealthy(): bool
    {
        return 0 === count($this->errors);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
