<?php

namespace App\Entity;

use App\Repository\EmploymentDisputeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmploymentDisputeRepository::class)]
class EmploymentDispute
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    private string $id;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $memorableWord;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $modified;

    #[ORM\Column(type: 'string', length: 20)]
    private string $representing;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $contactMethod;

    #[ORM\Column(type: 'string', length: 80, nullable: true)]
    private ?string $verificationContact = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    /**
     * @var Collection<int, CaseNumber>
     */
    #[ORM\OneToMany(mappedBy: 'employmentDispute', targetEntity: CaseNumber::class, orphanRemoval: true)]
    private Collection $caseNumbers;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $submissionDateTime;

    /**
     * @var Collection<int, EmploymentDisputeSubmission>
     */
    #[ORM\OneToMany(mappedBy: 'employmentDispute', targetEntity: EmploymentDisputeSubmission::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $employmentDisputeSubmissions;

    #[ORM\OneToMany(mappedBy: 'employmentDispute', targetEntity: SubmissionSearchIndexItemFirstName::class, orphanRemoval: true, cascade: ['remove'])]
    private $searchIndexItemsFirstName;

    #[ORM\OneToMany(mappedBy: 'employmentDispute', targetEntity: SubmissionSearchIndexItemLastName::class, orphanRemoval: true, cascade: ['remove'])]
    private $searchIndexItemsLastName;

    #[ORM\OneToMany(mappedBy: 'employmentDispute', targetEntity: SubmissionSearchIndexItemRespondentName::class, orphanRemoval: true, cascade: ['remove'])]
    private $searchIndexItemsRespondentName;

    #[ORM\Column(type: 'integer')]
    private $numberOfReturns = 0;

    public function __construct()
    {
        $this->employmentDisputeSubmissions = new ArrayCollection();
        $this->caseNumbers = new ArrayCollection();
        $this->searchIndexItemsFirstName = new ArrayCollection();
        $this->searchIndexItemsLastName = new ArrayCollection();
        $this->searchIndexItemsRespondentName = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    public function getMemorableWord(): ?string
    {
        return $this->memorableWord;
    }

    public function setMemorableWord(string $memorableWord): self
    {
        $this->memorableWord = strtoupper($memorableWord);

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     */
    public function setData(array $data): self
    {
        $this->setModified(new \DateTime());
        $this->data = $data;

        return $this;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getModified(): \DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }

    public function getRepresenting(): ?string
    {
        return $this->representing;
    }

    public function setRepresenting(string $representing): self
    {
        $this->representing = $representing;

        return $this;
    }

    public function getContactMethod(): ?string
    {
        return $this->contactMethod;
    }

    public function setContactMethod(?string $contactMethod): self
    {
        $this->contactMethod = $contactMethod;

        return $this;
    }

    public function hasSelectedContact(string $contactMethod): bool
    {
        if (null === $this->contactMethod) {
            return false;
        }

        $contactMethods = explode('-', $this->contactMethod);

        return in_array($contactMethod, $contactMethods);
    }

    public function getVerificationContact(): ?string
    {
        return $this->verificationContact;
    }

    public function setVerificationContact(?string $verificationContact): self
    {
        $this->verificationContact = $verificationContact;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCaseNumberList(): ?string
    {
        $caseNumberList = [];
        foreach ($this->caseNumbers as $caseNumber) {
            $caseNumberList[] = $caseNumber->getCaseRefNumber();
        }

        return implode(', ', $caseNumberList);
    }

    public function getSubmissionDateTime(): ?\DateTimeInterface
    {
        return $this->submissionDateTime ?? new \DateTime();
    }

    public function setSubmissionDateTime(?\DateTimeInterface $submissionDateTime): self
    {
        $this->submissionDateTime = $submissionDateTime;

        return $this;
    }

    public function retrieveSubmissionDateTime(): ?\DateTimeInterface
    {
        return $this->submissionDateTime;
    }

    /**
     * @return Collection<int, EmploymentDisputeSubmission>
     */
    public function getEmploymentDisputeSubmissions(): Collection
    {
        return $this->employmentDisputeSubmissions;
    }

    public function getLastErrorMessage(): array
    {
        $lastSubmission = $this->employmentDisputeSubmissions->last();
        if ($lastSubmission) {
            $data = $lastSubmission->getResponse();
            $statusCode = '/^HTTP\/1\.1\s(?<statusCode>\d+)\s/';
            $errorCode = '/\"code\":\"(?<errorCode>\w+)\"\,/';
            $statusMatches = [];
            preg_match($statusCode, $data, $statusMatches);
            $errorCodeMatches = [];
            preg_match($errorCode, $data, $errorCodeMatches);

            return [
                'code' => $statusMatches['statusCode'] ?? 500,
                'error' => $errorCodeMatches['errorCode'] ?? 'Unknown',
            ];
        }

        return [];
    }

    public function addEmploymentDisputeSubmission(EmploymentDisputeSubmission $employmentDisputeSubmission): self
    {
        if (!$this->employmentDisputeSubmissions->contains($employmentDisputeSubmission)) {
            $this->employmentDisputeSubmissions[] = $employmentDisputeSubmission;
            $employmentDisputeSubmission->setEmploymentDispute($this);
        }

        return $this;
    }

    public function removeEmploymentDisputeSubmission(EmploymentDisputeSubmission $employmentDisputeSubmission): self
    {
        $this->employmentDisputeSubmissions->removeElement($employmentDisputeSubmission);

        return $this;
    }

    public function isRetryable(): bool
    {
        return in_array($this->getStatus(), [self::STATUS_FAILED, self::STATUS_QUEUED, self::STATUS_PAUSED]);
    }

    /**
     * @return Collection<int, CaseNumber>
     */
    public function getCaseNumbers(): Collection
    {
        return $this->caseNumbers;
    }

    public function addCaseNumber(CaseNumber $caseNumber): self
    {
        if (!$this->caseNumbers->contains($caseNumber)) {
            $this->caseNumbers[] = $caseNumber;
            $caseNumber->setEmploymentDispute($this);
        }

        return $this;
    }

    public function removeCaseNumber(CaseNumber $caseNumber): self
    {
        if ($this->caseNumbers->removeElement($caseNumber)) {
            // set the owning side to null (unless already changed)
            if ($caseNumber->getEmploymentDispute() === $this) {
                $caseNumber->setEmploymentDispute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubmissionSearchIndexItemFirstName>
     */
    public function getSearchIndexItemsFirstName(): Collection
    {
        return $this->searchIndexItemsFirstName;
    }

    public function addSearchIndexItemFirstName(SubmissionSearchIndexItemFirstName $searchIndexItem): self
    {
        if (!$this->searchIndexItemsFirstName->contains($searchIndexItem)) {
            $this->searchIndexItemsFirstName[] = $searchIndexItem;
            $searchIndexItem->setEmploymentDispute($this);
        }

        return $this;
    }

    public function removeSearchIndexItemFirstName(SubmissionSearchIndexItemFirstName $searchIndexItem): self
    {
        if ($this->searchIndexItemsFirstName->removeElement($searchIndexItem)) {
            // set the owning side to null (unless already changed)
            if ($searchIndexItem->getEmploymentDispute() === $this) {
                $searchIndexItem->setEmploymentDispute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubmissionSearchIndexItemLastName>
     */
    public function getSearchIndexItemsLastName(): Collection
    {
        return $this->searchIndexItemsLastName;
    }

    public function addSearchIndexItemLastName(SubmissionSearchIndexItemLastName $searchIndexItem): self
    {
        if (!$this->searchIndexItemsLastName->contains($searchIndexItem)) {
            $this->searchIndexItemsLastName[] = $searchIndexItem;
            $searchIndexItem->setEmploymentDispute($this);
        }

        return $this;
    }

    public function removeSearchIndexItemLastName(SubmissionSearchIndexItemLastName $searchIndexItem): self
    {
        if ($this->searchIndexItemsLastName->removeElement($searchIndexItem)) {
            // set the owning side to null (unless already changed)
            if ($searchIndexItem->getEmploymentDispute() === $this) {
                $searchIndexItem->setEmploymentDispute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubmissionSearchIndexItemRespondentName>
     */
    public function getSearchIndexItemsRespondentName(): Collection
    {
        return $this->searchIndexItemsRespondentName;
    }

    public function addSearchIndexItemRespondentName(SubmissionSearchIndexItemRespondentName $searchIndexItem): self
    {
        if (!$this->searchIndexItemsRespondentName->contains($searchIndexItem)) {
            $this->searchIndexItemsRespondentName[] = $searchIndexItem;
            $searchIndexItem->setEmploymentDispute($this);
        }

        return $this;
    }

    public function removeSearchIndexItemRespondentName(SubmissionSearchIndexItemRespondentName $searchIndexItem): self
    {
        if ($this->searchIndexItemsRespondentName->removeElement($searchIndexItem)) {
            // set the owning side to null (unless already changed)
            if ($searchIndexItem->getEmploymentDispute() === $this) {
                $searchIndexItem->setEmploymentDispute(null);
            }
        }

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

    public function incrementNumberOfReturns(): self
    {
        ++$this->numberOfReturns;

        return $this;
    }
}
