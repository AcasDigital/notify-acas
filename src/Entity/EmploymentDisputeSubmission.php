<?php

namespace App\Entity;

use App\Repository\EmploymentDisputeSubmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmploymentDisputeSubmissionRepository::class)]
class EmploymentDisputeSubmission
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: EmploymentDispute::class, inversedBy: 'employmentDisputeSubmissions')]
    #[ORM\JoinColumn(nullable: false)]
    private EmploymentDispute $employmentDispute;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $payload;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmploymentDispute(): ?EmploymentDispute
    {
        return $this->employmentDispute;
    }

    public function setEmploymentDispute(EmploymentDispute $employmentDispute): self
    {
        $this->employmentDispute = $employmentDispute;

        return $this;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): self
    {
        $this->payload = $payload;

        return $this;
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

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
