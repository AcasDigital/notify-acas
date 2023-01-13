<?php

namespace App\Entity;

use App\Repository\UsageReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsageReportRepository::class)]
class UsageReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'datetime')]
    private $submitted;

    #[ORM\Column(type: 'integer')]
    private $timeToSubmission;

    #[ORM\Column(type: 'string', length: 255)]
    private $journeyType;

    #[ORM\Column(type: 'boolean')]
    private $emailProvided;

    #[ORM\Column(type: 'string', length: 255)]
    private $representative;

    #[ORM\Column(type: 'boolean')]
    private $memorableWordProvided;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $reasonForDisputeCount;

    #[ORM\Column(type: 'integer')]
    private $numberOfReturns;

    #[ORM\Column(type: 'string', length: 255)]
    private $contactMethod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getSubmitted(): ?\DateTimeInterface
    {
        return $this->submitted;
    }

    public function setSubmitted(\DateTimeInterface $submitted): self
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function getTimeToSubmission(): ?int
    {
        return $this->timeToSubmission;
    }

    public function setTimeToSubmission(int $timeToSubmission): self
    {
        $this->timeToSubmission = $timeToSubmission;

        return $this;
    }

    public function getJourneyType(): ?string
    {
        return $this->journeyType;
    }

    public function setJourneyType(string $journeyType): self
    {
        $this->journeyType = $journeyType;

        return $this;
    }

    public function getEmailProvided(): ?bool
    {
        return $this->emailProvided;
    }

    public function setEmailProvided(bool $emailProvided): self
    {
        $this->emailProvided = $emailProvided;

        return $this;
    }

    public function getRepresentative(): ?string
    {
        return $this->representative;
    }

    public function setRepresentative(string $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getMemorableWordProvided(): ?bool
    {
        return $this->memorableWordProvided;
    }

    public function setMemorableWordProvided(bool $memorableWordProvided): self
    {
        $this->memorableWordProvided = $memorableWordProvided;

        return $this;
    }

    public function getReasonForDisputeCount(): ?int
    {
        return $this->reasonForDisputeCount;
    }

    public function setReasonForDisputeCount(?int $reasonForDisputeCount): self
    {
        $this->reasonForDisputeCount = $reasonForDisputeCount;

        return $this;
    }

    public function getNumberOfReturns(): ?int
    {
        return $this->numberOfReturns;
    }

    public function setNumberOfReturns(int $numberOfReturns): self
    {
        $this->numberOfReturns = $numberOfReturns;

        return $this;
    }

    public function getContactMethod(): ?string
    {
        return $this->contactMethod;
    }

    public function setContactMethod(string $contactMethod): self
    {
        $this->contactMethod = $contactMethod;

        return $this;
    }
}
