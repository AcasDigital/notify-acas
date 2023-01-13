<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\TaskList\EmploymentDisputeData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Translation\TranslatorService;

interface RepeatedSectionInterface
{
    public function getType(): string;

    public function getLabel(): string;

    public function getReviewPageLabel(): string;

    public function generateId(): string;

    public function setId(string $id): void;

    public function initialize(TranslatorService $translator, TaskOptions $taskOptions, EmploymentDisputeData $disputeForm): void;
}
