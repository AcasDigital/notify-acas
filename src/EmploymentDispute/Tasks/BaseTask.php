<?php

namespace App\EmploymentDispute\Tasks;

use App\Translation\TranslatorService;

class BaseTask
{
    protected string $sectionId;
    protected TranslatorService $translator;
    protected TaskOptions $options;

    public function __construct()
    {
    }

    public function initialize(TranslatorService $translator, TaskOptions $options): void
    {
        $this->translator = $translator;
        $this->options = $options;
    }

    public function getSectionId(): string
    {
        return $this->sectionId;
    }

    public function setSectionId(string $id): void
    {
        $this->sectionId = $id;
    }

    public function getTemplate(): string
    {
        return 'app/task_base.html.twig';
    }

    public function getLabel(): string
    {
        return 'Base Task';
    }

    public function getTitle(): string
    {
        return $this->getLabel();
    }

    public function getReviewPageLabel(): string
    {
        return $this->getLabel();
    }

    public function getDataKey(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return array<string, string>
     */
    public function getTemplateOptions(): array
    {
        return [];
    }
}
