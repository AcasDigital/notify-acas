<?php

namespace App\EmploymentDispute\Wizard;

use Symfony\Component\Serializer\Annotation\Ignore;

class WizardData
{
    private ?int $currentStep = null;
    /**
     * @var array<array<string,mixed>>
     */
    private array $tasks;

    private ?string $verificationCode = null;
    private ?\DateTime $verificationCodeStartDate = null;
    private ?string $status = null;

    public function getCurrentStep(): ?int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(?int $currentStep): self
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    #[Ignore]
    public function getTaskData(string $id): array
    {
        return $this->tasks[$id] ?? [];
    }

    /**
     * @param array<string, mixed> $taskData
     */
    public function setTaskData(string $id, array $taskData): self
    {
        $this->tasks[$id] = $taskData;

        return $this;
    }

    public function removeTaskData(string $id): self
    {
        $taskData = $this->tasks[$id] ?? null;
        if (!is_null($taskData)) {
            unset($this->tasks[$id]);
        }

        return $this;
    }

    /**
     * @return array<array<string,mixed>>
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param array<array<string, mixed>> $tasks
     */
    public function setTasks(array $tasks): self
    {
        $this->tasks = $tasks;

        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getVerificationCodeStartDate(): ?\DateTime
    {
        return $this->verificationCodeStartDate;
    }

    public function setVerificationCodeStartDate(?\DateTime $date): self
    {
        $this->verificationCodeStartDate = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
