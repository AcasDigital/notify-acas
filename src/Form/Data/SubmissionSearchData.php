<?php

namespace App\Form\Data;

use App\EmploymentDispute\Submission\Search\SearchFiltersInterface;
use Doctrine\ORM\QueryBuilder;

class SubmissionSearchData implements SearchFiltersInterface
{
    private ?string $caseNumber = null;
    private ?string $status = null;
    private ?string $claimentOrRepFirstName = null;
    private ?string $claimentOrRepLastName = null;
    private ?string $respondentName = null;
    private ?string $returnCode = null;
    private ?\DateTime $dateFrom = null;
    private ?\DateTime $dateTo = null;
    private ?string $hasFailedSubmissions = null;

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(?string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getClaimentOrRepFirstName(): ?string
    {
        return $this->claimentOrRepFirstName;
    }

    public function setClaimentOrRepFirstName(?string $claimentOrRepFirstName): self
    {
        $this->claimentOrRepFirstName = $claimentOrRepFirstName;

        return $this;
    }

    public function getClaimentOrRepLastName(): ?string
    {
        return $this->claimentOrRepLastName;
    }

    public function setClaimentOrRepLastName(?string $claimentOrRepLastName): self
    {
        $this->claimentOrRepLastName = $claimentOrRepLastName;

        return $this;
    }

    public function getRespondentName(): ?string
    {
        return $this->respondentName;
    }

    public function setRespondentName(?string $respondentName): self
    {
        $this->respondentName = $respondentName;

        return $this;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTime $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTime $dateTo): self
    {
        $this->dateTo = $dateTo?->setTime(23, 59, 59);

        return $this;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function setReturnCode(?string $returnCode): self
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function filtersApplied(): bool
    {
        return $this->caseNumber
            || $this->dateFrom
            || $this->dateTo
            || $this->claimentOrRepFirstName
            || $this->claimentOrRepLastName
            || $this->respondentName
            || $this->returnCode
            || $this->hasFailedSubmissions
        ;
    }

    public function applyFilters(QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->status) {
            $queryBuilder
                ->andWhere('d.status = :status')
                ->setParameter('status', $this->status);
        }

        if ($this->dateFrom) {
            $queryBuilder
                ->andWhere('d.submissionDateTime > :dateFrom')
                ->setParameter('dateFrom', $this->dateFrom);
        }

        if ($this->dateTo) {
            $queryBuilder
                ->andWhere('d.submissionDateTime < :dateTo')
                ->setParameter('dateTo', $this->dateTo);
        }

        if ($this->returnCode) {
            $queryBuilder
                ->andWhere('d.id = :returnCode')
                ->setParameter('returnCode', $this->returnCode);
        }

        if ($this->caseNumber) {
            $queryBuilder
                ->leftJoin('d.caseNumbers', 'n')
                ->andWhere('n.caseRefNumber = :caseNumber')
                ->setParameter('caseNumber', $this->caseNumber);
        }

        if ($this->claimentOrRepFirstName) {
            $queryBuilder
                ->leftJoin('d.searchIndexItemsFirstName', 'fn')
                ->andWhere('fn.keyword = :firstName')
                ->setParameter('firstName', $this->claimentOrRepFirstName);
        }

        if ($this->claimentOrRepLastName) {
            $queryBuilder
                ->leftJoin('d.searchIndexItemsLastName', 'ln')
                ->andWhere('ln.keyword = :lastName')
                ->setParameter('lastName', $this->claimentOrRepLastName);
        }

        if ($this->respondentName) {
            $queryBuilder
                ->leftJoin('d.searchIndexItemsRespondentName', 'rn')
                ->andWhere('rn.keyword = :respondentName')
                ->setParameter('respondentName', $this->respondentName);
        }

        $failedSubmissionFilters = ['yes', 'no'];
        if (in_array($this->hasFailedSubmissions, $failedSubmissionFilters)) {
            $queryBuilder
                ->leftJoin('d.employmentDisputeSubmissions', 'subm');
            if ('no' === $this->hasFailedSubmissions) {
                $queryBuilder
                ->andWhere('subm.status = :submStatus')
                ->setParameter('submStatus', 'success');
            } elseif ('yes' === $this->hasFailedSubmissions) {
                $queryBuilder
                ->andWhere('subm.status = :submStatus')
                ->setParameter('submStatus', 'error');
            }
        }

        return $queryBuilder;
    }

    /**
     * Get the value of hasFailedSubmissions.
     */
    public function getHasFailedSubmissions(): ?string
    {
        return $this->hasFailedSubmissions;
    }

    /**
     * Set the value of hasFailedSubmissions.
     */
    public function setHasFailedSubmissions(?string $hasFailedSubmissions): self
    {
        $this->hasFailedSubmissions = $hasFailedSubmissions;

        return $this;
    }
}
