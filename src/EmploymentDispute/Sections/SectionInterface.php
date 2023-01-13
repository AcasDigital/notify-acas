<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeData;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Translation\TranslatorService;

interface SectionInterface
{
    public function getId(): string;

    public function getLabel(): string;

    public function getReviewPageLabel(): string;

    public function getTask(string $sectionId, string $taskId): ?TaskInterface;

    /**
     * @return array<TaskInterface>
     */
    public function getTasks(): array;

    public function initializeTasks(TaskOptions $taskOptions): void;

    public function initialize(TranslatorService $translator, TaskOptions $taskOptions, EmploymentDisputeData $disputeForm): void;

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void;
}
