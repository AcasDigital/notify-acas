<?php

namespace App\EmploymentDispute\Tasks;

use App\Translation\TranslatorService;
use Symfony\Component\Form\FormBuilderInterface;

interface TaskInterface
{
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_OPTIONAL = 'optional';
    public const STATUS_NOT_STARTED = 'not-started';
    public const STATUS_NOT_VALID = 'not-valid';

    public function buildForm(FormBuilderInterface $formBuilder): FormBuilderInterface;

    public function getTemplate(): string;

    /**
     * @return array<string, string>
     */
    public function getTemplateOptions(): array;

    /**
     * Get task list label.
     */
    public function getLabel(): string;

    /**
     * Get the review page label.
     */
    public function getReviewPageLabel(): string;

    /**
     * Get task page title.
     */
    public function getTitle(): string;

    public function getDataKey(): string;

    public function getDataClass(): string;

    public function getId(): string;

    public function setSectionId(string $id): void;

    public function getSectionId(): string;

    public function initialize(TranslatorService $translator, TaskOptions $options): void;

    public function getWeight(): int;
}
