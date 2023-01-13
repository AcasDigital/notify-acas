<?php

namespace App\EmploymentDispute\TaskList;

use Symfony\Component\Validator\Constraints as Assert;

class EmploymentDisputeData
{
    /**
     * @var array<string,array<string,array<string,mixed>>>
     */
    #[Assert\Valid]
    private array $sectionData = [];

    /**
     * @var array<string,string>
     */
    private array $repeatedSectionList = [];

    /**
     * @return array<string,array<string,array<string,mixed>>>
     */
    public function getSectionData(): array
    {
        return $this->sectionData;
    }

    /**
     * @param array<string,array<string,array<string,mixed>>> $sectionData
     */
    public function setSectionData(array $sectionData): self
    {
        $this->sectionData = $sectionData;

        return $this;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function updateTaskData(string $section, string $task, array $data): self
    {
        $this->sectionData[$section][$task] = $data;

        return $this;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function retrieveTaskData(string $section, string $task): ?array
    {
        return $this->sectionData[$section][$task] ?? null;
    }

    public function addRepeatedSection(string $type, string $id): void
    {
        $this->repeatedSectionList[$id] = $type;
    }

    public function removeRepeatedSection(string $id): void
    {
        unset($this->repeatedSectionList[$id]);
        unset($this->sectionData[$id]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRepeatedSectionList(): array
    {
        return $this->repeatedSectionList;
    }

    /**
     * @param array<string,string> $repeatedSectionList
     */
    public function setRepeatedSectionList(array $repeatedSectionList): self
    {
        $this->repeatedSectionList = $repeatedSectionList;

        return $this;
    }

    public function getRespondentSectionData(): array
    {
        $allSectionsData = $this->getSectionData();
        $respondentData = [];
        foreach ($allSectionsData as $key => $sectionData) {
            if (str_contains($key, 'respondent')) {
                $respondentData[] = $sectionData;
            }
        }

        return $respondentData;
    }
}
