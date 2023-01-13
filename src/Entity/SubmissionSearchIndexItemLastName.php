<?php

namespace App\Entity;

use App\Repository\SubmissionSearchIndexItemLastNameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionSearchIndexItemLastNameRepository::class)]
#[ORM\Index(name: 'lastname_search_idx', columns: ['keyword', 'section'])]
class SubmissionSearchIndexItemLastName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $keyword;

    #[ORM\Column(type: 'string', length: 255)]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $section;

    #[ORM\ManyToOne(targetEntity: EmploymentDispute::class, inversedBy: 'searchIndexItemsLastName')]
    private $employmentDispute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getEmploymentDispute(): ?EmploymentDispute
    {
        return $this->employmentDispute;
    }

    public function setEmploymentDispute(?EmploymentDispute $employmentDispute): self
    {
        $this->employmentDispute = $employmentDispute;

        return $this;
    }
}
