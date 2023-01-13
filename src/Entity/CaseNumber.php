<?php

namespace App\Entity;

use App\Repository\CaseNumberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaseNumberRepository::class)]
class CaseNumber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 30)]
    private string $caseRefNumber;

    #[ORM\ManyToOne(targetEntity: EmploymentDispute::class, inversedBy: 'caseNumbers')]
    private ?EmploymentDispute $employmentDispute;

    #[ORM\Column(type: 'string', length: 255)]
    private string $respondentName;

    #[ORM\Column(type: 'string', length: 30)]
    private string $respondentType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaseRefNumber(): ?string
    {
        return $this->caseRefNumber;
    }

    public function setCaseRefNumber(string $caseRefNumber): self
    {
        $this->caseRefNumber = $caseRefNumber;

        return $this;
    }

    public function getEmploymentDispute(): EmploymentDispute
    {
        return $this->employmentDispute;
    }

    public function setEmploymentDispute(EmploymentDispute $employmentDispute): self
    {
        $this->employmentDispute = $employmentDispute;

        return $this;
    }

    public function getRespondentName(): string
    {
        return $this->respondentName;
    }

    public function setRespondentName(string $respondentName): self
    {
        $this->respondentName = $respondentName;

        return $this;
    }

    public function getRespondentType(): string
    {
        return $this->respondentType;
    }

    public function setRespondentType(string $respondentType): self
    {
        $this->respondentType = $respondentType;

        return $this;
    }
}
